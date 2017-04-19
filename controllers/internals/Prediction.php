<?php
namespace controllers\internals;

class Prediction extends \Controller
{
    //Const for score calculation
    
    //news
    const NEWS_DEFAULT_SCORE = 100;
    const NEWS_VISIBILITY = 15000000;
    const NEWS_CONFIDENCE = 0.55;

    //tweets
    const TWEET_DEFAULT_SCORE = 100;
    const TWEET_VISIBILITY = 5000000;
    const TWEET_CONFIDENCE = 0.3;

    //Community
    const COMMUNITY_DEFAULT_SCORE = 100;
    const COMMUNITY_PERCENT_COEF = 0.1;

    /**
     * This function run a prediction for all candidat anf for today
     */
    public function runPredictionsForToday()
    {
        $now = new \DateTime();
        $todayBeginning = new \DateTime();
        $todayBeginning->setTime(0, 0, 0);
        $todayEnd = new \DateTime();
        $todayEnd->setTime(23, 59, 59);

        global $candidats;

        foreach ($candidats as $candidat)
        {
            $candidatScores = $this->calculateCandidatScoresBetweenDates($candidat, $todayBeginning, $todayEnd);

            $prediction = [
                'candidat' => $candidat['name'],
                'score' => $candidatScores['score'],
                'tweet_score' => $candidatScores['tweet_score'],
                'news_score' => $candidatScores['news_score'],
                'survey_score' => $candidatScores['survey_score'],
                'community_score' => $candidatScores['community_score'],
                'at' => $now->format('Y-m-d H:i:s'),
            ];

            echo $candidat['real_name'] . " : " . $candidatScores['score'] . "\n";
            continue;

            $isInsertSuccess = $this->insertPrediction($prediction);

            if (!$isInsertSuccess)
            {
                echo "Fail inserting prediction for " . $candidat['name'] . "\n";
            }
            else
            {
                echo "Success inserting prediction for " . $candidat['name'] . "\n";
            }
        }


    }

    /**
     * This function insert a new prediction for a candidat
     */
    public function insertPrediction($prediction)
    {
        global $bdd;
        $model = new \Model($bdd);
        return $model->insertIntoTable('prediction', $prediction);
    }

    /**
     * This function calculate all scores of a candidat between two dates
     */
    public function calculateCandidatScoresBetweenDates($candidat, \DateTime $startDate, \DateTime $endDate)
    {
        $predictionTweet = new PredictionTweet();
        $tweetsScore = $predictionTweet->calculateCandidatScoreBetweenDatesAsPercent($candidat, $startDate, $endDate);

        $predictionNews = new PredictionNews();
        $newsScore = $predictionNews->calculateCandidatNewsScoreBetweenDatesAsPercent($candidat, $startDate, $endDate);

        $predictionCommunity = new PredictionCommunity();
        $meanCommunityScoreAsPercent = $predictionCommunity->calculateMeanCandidatCommunityAsPercentBetweenTwoDate($candidat, $startDate, $endDate);

        $predictionSurvey = new PredictionSurvey();
        $meanSurveyScore = $predictionSurvey->calculateMeanCandidatSurveyScoreAsPercentBetweenTwoDate($candidat, $startDate, $endDate);

        $candidatScore = 0;

        //Tweets implication
        $candidatScore += self::TWEET_DEFAULT_SCORE * (1 + $tweetsScore) * self::TWEET_CONFIDENCE * (self::TWEET_VISIBILITY / self::NEWS_VISIBILITY);

        //News implication
        $candidatScore += self::NEWS_DEFAULT_SCORE * (1 + $newsScore) * self::NEWS_CONFIDENCE;

        //Community implication
        $candidatScore *= 1 + ($meanCommunityScoreAsPercent * self::COMMUNITY_PERCENT_COEF);

        //Official surveys implication
        $candidatScore *= 1 + $meanSurveyScore;

        return [
            'score' => $candidatScore,
            'tweet_score' => $tweetsScore,
            'news_score' => $newsScore,
            'survey_score' => $meanSurveyScore,
            'community_score' => $meanCommunityScoreAsPercent,
        ];
    }

}
