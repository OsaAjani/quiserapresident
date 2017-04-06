<?php
namespace controllers\internals;

class SurveyBot extends \Controller
{
    private $urlSurvey = 'http://elections.huffingtonpost.com/pollster/api/v2/charts/france-presidential-election-round-1';

    /**
     * This function search the last prevision for the election
     */
    public function searchForSurvey()
    {
        echo "Searching for survey...";

        //Using CURL to call the $urlSurvey and get the json
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $this->urlSurvey);
        $survey = curl_exec($ch);
        curl_close($ch);

        if (!$survey)
        {
            echo "fail.\n";
            exit(1);
        }

        //Decode JSON
        $survey = json_decode($survey);

        echo "find.\n";

        //We are calling processing
        $this->processingSurvey($survey);
    }

    /**
     * This function processing the survey to find the datas and insert them in database
     * @param JsonObject $survey : Le survey sous forme d'objet JSON
     */
    private function processingSurvey($survey)
    {
        echo "Processing survey...\n";

        //Find date of survey
        if (!$surveyDate = new \DateTime($survey->pollster_estimates[0]->datetime))
        {
            echo "Fail, impossible date.\n";
            exit(1);
        }

        $surveyDate = $surveyDate->format('Y-m-d H:i:s');

        //Processing each candidat
        global $candidats;
        global $bdd;
        $model = new \Model($bdd);

        foreach ($survey->pollster_estimates[0]->values->hash as $name => $score)
        {
            //Clear name and create surveyToInsert
            $clearName = mb_strtolower(TextAnalysis::removeAccent($name));
            
            $surveyToInsert = [
                'candidat' => $clearName,
                'value' => $score,
                'at' => $surveyDate,
            ];


            echo "  Try insert " . $clearName . "...";
            
            //If candidat not find in our list, or insert fail
            if (!isset($candidats[$clearName]) || !$model->insertIntoTable('survey', $surveyToInsert))
            {
                echo "ko.\n";
                continue;
            }
            
            echo "ok.\n";
        }

        echo "Done.\n";
    }

    /**
     * This function add a tweet to the queue to be processed later
     * @param string $tweet : The tweet to add to queue
     */
    private function addTweetToQueue($tweet)
    {
        //Open the queue and add the tweet
        $queue = msg_get_queue($this->queueId);

        //Keep only interesting data
        $tweet = [
            'id_tweet' => $tweet['id'],
            'content' => isset($tweet['extended_tweet']['full_text']) ? $tweet['extended_tweet']['full_text'] : $tweet['text'],
            'user' => $tweet['user'],
            'timestamp' => $tweet['timestamp_ms'],
            'is_response' => $tweet['is_quote_status'],
            'is_retweet' => isset($tweet['retweeted_status']),
            'lang' => $tweet['lang'],
        ];

        echo "Add tweet : " . $tweet['id_tweet'] . " to queue.\n";
        msg_send($queue, $this->queueMsgType, $tweet, true, false);
    }

    /**
     * This function read tweets from queue and for each of them, ask for processing
     */
    private function readTweetsFromQueue()
    {
        //open the queue
        $queue = msg_get_queue($this->queueId);

        $thisMsgType = NULL;
        $tweet = NULL;

        //wait for new message
        while (msg_receive($queue, $this->queueMsgType, $thisMsgType, 409600, $tweet))
        {
            //Ask for processing the tweet
            if ($this->processingTweet($tweet))
            {
                echo "...insert.\n";
            }
            else
            {
                echo "...ignore.\n";
            }

            //Reset the msgType and tweet content
            $thisMsgType = NULL;
            $tweet = NULL;
        }
    }

    /**
     * This function processing the Tweet to analyse this quality, sentiment, etc.
     * @param string $tweet : The tweet to process as json string
     */
    private function processingTweet($tweet)
    {
        global $candidats;

        echo "Processing tweet : " . $tweet['id_tweet'];

        //If tweet is a retweet, or a response we dont care
        if ($tweet['is_retweet'] || $tweet['is_response'])
        {
            return false;
        }

        //If tweet is not in french, we dont care
        if ($tweet['lang'] != 'fr')
        {
            return false;
        }

        //Searching who the tweet is about
        $whoIsAbout = TextAnalysis::whoIsAbout($candidats, $tweet['content']);

        //If we dont know, we dont care
        if ($whoIsAbout[array_keys($whoIsAbout)[0]] == 0 || ($whoIsAbout[array_keys($whoIsAbout)[0]] == $whoIsAbout[array_keys($whoIsAbout)[1]]))
        {
            return false;
        }

        reset($whoIsAbout);

        //Analysing tweet sentiment
        $sentimentAnalyser = new Sentiment(false, 'fr');
        $sentimentScore = $sentimentAnalyser->score($tweet['content']);
        $mainSentiment = $sentimentAnalyser->categorise($tweet['content']);

        //Who the author is for ?
        $whoAuthorIsFor = TextAnalysis::whoIsAbout($candidats, $tweet['user']['description']);

        //If author dont have any politics in his description, he is not politic
        $isAuthorPolitic = !($whoAuthorIsFor[array_keys($whoAuthorIsFor)[0]] == 0);

        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');

        //Create tweet array to insert
        $tweetToInsert = [
            'id_tweet' => $tweet['id_tweet'],
            'content' => $tweet['content'],
            'nb_like' => 0,
            'nb_view' => 0,
            'author_description' => $tweet['user']['description'],
            'author_certified' => $tweet['user']['verified'],
            'author_nb_followers' => $tweet['user']['followers_count'],
            'author_is_politic' => $isAuthorPolitic,
            'candidat' => key($whoIsAbout),
            'main_sentiment' => $mainSentiment,
            'main_sentiment_value' => $sentimentScore[$mainSentiment],
            'sentiments_value' => serialize($sentimentScore),
            'last_update' => $now,
            'at' => $now,
        ];

        global $bdd;
        $model = new \Model($bdd);
        return $model->insertIntoTable('tweet', $tweetToInsert);
    }

}
