<?php
namespace controllers\internals;

class PredictionTweet extends \Controller
{
    //Const for tweet score calculation
    const TWEET_SCORE_COEF_AUTHOR_CERTIFIED = 1.2;
    const TWEET_SCORE_COEF_MAIN_SENTIMENT_POS = 1;
    const TWEET_SCORE_COEF_MAIN_SENTIMENT_NEG = 0;
    const TWEET_SCORE_COEF_MAIN_SENTIMENT_NEU = 0.2;
    const TWEET_SCORE_COEF_AUTHOR_POLITIC = 0.4; 

    /**
     * This function return the tweet score of a candidat as a percentage (for use in total score calculation algorithm, not a real percentage of candidat score actually)
     */
    public function calculateCandidatScoreBetweenDatesAsPercent ($candidat, \DateTime $startDate, \DateTime $endDate)
    {
        $candidatScore = $this->calculateCandidatScoreBetweenDates($candidat, $startDate, $endDate);
        $totalScore = $this->calculateTotalScoreBetweenDates($startDate, $endDate);

        $tweets = $this->getAllTweetsForCandidatBetweenDates($candidat, $startDate, $endDate);
        $negativesTweets = $this->getAllTweetsForCandidatBetweenDates($candidat, $startDate, $endDate);
        $negativesTweetsPercent = count($negativesTweets) / count($tweets);

        $candidatScore = $candidatScore * (1 - $negativesTweetsPercent); //For balancing $candidat score when too much negativ tweets
        
        $candidatScoreAsPercent = $candidatScore / ($totalScore + 1);

        return $candidatScoreAsPercent; 
    }

    /**
     * This function calculate total tweets score for all candidats between two date
     */
    public function calculateTotalScoreBetweenDates (\DateTime $startDate, \DateTime $endDate)
    {
        global $candidats;

        $totalScore = 0;

        foreach ($candidats as $candidat)
        {
            $totalScore += $this->calculateCandidatScoreBetweenDates ($candidat, $startDate, $endDate);
        }

        return $totalScore;
    }

    /**
     * This function calculate total score of all Tweets beetwen two date and calculate score
     */
    public function calculateCandidatScoreBetweenDates ($candidat, \DateTime $startDate, \DateTime $endDate)
    {
        $score = 0;

        $tweets = $this->getAllTweetsForCandidatBetweenDates($candidat, $startDate, $endDate);

        foreach ($tweets as $tweet)
        {
            $tweetScore = $this->calculateTweetScore($tweet);
            $score += $tweetScore;
        }

        return $score;
    }

    /**
     * This function return all tweets of a candidat between two date
     */
    public function getAllTweetsForCandidatBetweenDates ($candidat, \DateTime $startDate, \DateTime $endDate)
    {
        global $bdd;
        $model = new \Model($bdd);
        return $model->getFromTableWhere('tweet', ['>=at' => $startDate->format('Y-m-d H:i:s'), '<=at' => $endDate->format('Y-m-d H:i:s'), 'candidat' => $candidat['name']]);
    }

    /**
     * This function return all negatives tweets of a candidat between two date
     */
    public function getAllNegativesTweetsForCandidatBetweenDates ($candidat, \DateTime $startDate, \DateTime $endDate)
    {
        global $bdd;
        $model = new \Model($bdd);
        return $model->getFromTableWhere('tweet', ['>=at' => $startDate->format('Y-m-d H:i:s'), '<=at' => $endDate->format('Y-m-d H:i:s'), 'candidat' => $candidat['name'], 'main_sentiment' => 'neg']);
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
        
        if ($tweet['author_is_politic'])
        {
            $score *= self::TWEET_SCORE_COEF_AUTHOR_POLITIC;
        }

        if ($tweet['main_sentiment'] == 'pos')
        {
            $score *= self::TWEET_SCORE_COEF_MAIN_SENTIMENT_POS;
        }
        elseif ($tweet['main_sentiment'] == 'neu') 
        {
            $score *= self::TWEET_SCORE_COEF_MAIN_SENTIMENT_NEG;
        }
        elseif ($tweet['main_sentiment'] == 'neg')
        {
            $score *= self::TWEET_SCORE_COEF_MAIN_SENTIMENT_NEU;
        }

        return $score;
    }

}
