<?php
namespace controllers\internals;

use OwlyCode\StreamingBird\StreamReader;
use OwlyCode\StreamingBird\StreamingBird;
use PHPInsight\Sentiment;

class TwitterBot extends \Controller
{
    private $queueId = 64958;
    private $queueMsgType = 42;

    /**
     * This function create bot for searching new tweets for our targeteds keywords
     */
    public function searchForNewTweets()
    {
        //Create table of terms to search
        global $candidats;
        $termsToSearch = [];

        foreach ($candidats as $candidat)
        {
            foreach ($candidat['keywords'] as $keywordsCoef => $keywords)
            {
                $termsToSearch = array_merge($termsToSearch, $keywords);
            }
        }

        //Connect to twitter streaming API and search for all intersting terms. If find one, add it to the queue
        $twitterConnection = new StreamingBird(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, TWITTER_OAUTH_TOKEN, TWITTER_OAUTH_SECRET);
        $twitterConnection->createStreamReader(StreamReader::METHOD_FILTER)->setTrack($termsToSearch)->consume(
            function ($tweet)
            {
                $this->addTweetToQueue($tweet);
            }
        );
    }

    /**
     * This function wait we found new tweets to analyse them
     */
    public function analyseNewTweets()
    {
            $this->readTweetsFromQueue();
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
