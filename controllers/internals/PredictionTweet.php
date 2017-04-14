<?php
namespace controllers\internals;

class PredictionTweet extends \Controller
{
    //Const for tweet score calculation
    const TWEET_SCORE_COEF_AUTHOR_CERTIFIED = 1.2;
    const TWEET_SCORE_COEF_MAIN_SENTIMENT_POS = 1;
    const TWEET_SCORE_COEF_MAIN_SENTIMENT_NEG = -1;
    const TWEET_SCORE_COEF_MAIN_SENTIMENT_NEU = 0.2;
    const TWEET_SCORE_COEF_AUTHOR_POLITIC = 0.4;

    /**
     * This function calculate total score of all Tweets beetwen two date and calculate score
     */
    public function calculateAllTweetsScoreForCandidatBetweenDates ($candidat, \DateTime $startDate, \DateTime $endDate)
    {
        $totalScore = 0;
        $tweets = $this->getAllTweetsForCandidatBetweenDates($candidat, $startDate, $endDate);

        foreach ($tweets as $tweet) {
            $totalScore += $this->calculateTweetScore($tweet);
        }

        return $totalScore;
    }

    /**
     * This function return all tweets between two date
     */
    public function getAllTweetsForCandidatBetweenDates ($candidat, \DateTime $startDate, \DateTime $endDate)
    {
        global $bdd;
        $model = new \Model($bdd);
        return $model->getFromTableWhere('tweet', ['>=at' => $startDate->format('Y-m-d H:i:s'), '<=at' => $endDate->format('Y-m-d H:i:s'), 'candidat' => $candidat['name']]);
    }

    /**
     * This function calculate score of a tweet
     */
    public function calculateTweetScore ($tweet)
    {
        $score = 1;

        $score *= 1 + ($tweet['author_nb_followers'] / 1000);

        if ($tweet['author_certified']) 
        {
            $score *= self::TWEET_SCORE_COEF_AUTHOR_CERTIFIED;
        }

        if ($tweet['main_sentiment'] == 'pos')
        {
            $score *= self::TWEET_SCORE_COEF_MAIN_SENTIMENT_POS;
        }
        elseif ($tweet['main_sentiment'] == 'neg')
        {
            $score *= self::TWEET_SCORE_COEF_MAIN_SENTIMENT_NEG;
        }
        elseif ($tweet['main_sentiment'] == 'neu') 
        {
            $score *= self::TWEET_SCORE_COEF_MAIN_SENTIMENT_NEU;
        }

        if ($tweet['author_is_politic']) {
            $score *= self::TWEET_SCORE_COEF_AUTHOR_POLITIC;
        }

        return $score;
    }

}
