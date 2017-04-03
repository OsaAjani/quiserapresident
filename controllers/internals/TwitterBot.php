<?php
namespace controllers\internals;

use OwlyCode\StreamingBird\StreamReader;
use OwlyCode\StreamingBird\StreamingBird;

class TwitterBot extends \Controller
{
    private $queueId = 64958;
    private $queueMsgType = 42;

    /**
     * This function create bot for searching new tweets for our targeteds keywords
     */
    public function searchForNewTweets()
    {
        $termsToSearch = ['melenchon'];

        //Connect to twitter streaming API and search for all intersting terms. If find one, add it to the queue
        $twitterConnection = new StreamingBird(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, TWITTER_OAUTH_TOKEN, TWITTER_OAUTH_SECRET);
        $twitterConnection->createStreamReader(StreamReader::METHOD_FILTER)->setTrack($termsToSearch)->consume(
            function ($tweet)
            {
                //var_dump($tweet);
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
            'content' => $tweet['text'],
            'user' => $tweet['user'],
            'timestamp' => $tweet['timestamp_ms'],
        ];

        var_dump($tweet['id_tweet']);
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
            $this->processingTweet($tweet);

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
        var_dump($tweet);
    }

}
