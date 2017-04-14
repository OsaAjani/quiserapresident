<?php
namespace controllers\internals;

class Prediction extends \Controller
{
    //Const for score calculation
    
    //news
    const NEWS_VISIBILITY = 15000000;
    const NEWS_CONFIDENCE = 0.55;

    //tweets
    const TWEET_VISIBILITY = 5000000;
    const TWEET_CONFIDENCE = 0.3;

    //Community
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
            $candidatScore = $this->calculateCandidatScoreBetweenDates($candidat, $todayBeginning, $todayEnd);

            $prediction = [
                'candidat' => $candidat['name'],
                'score' => $candidatScore,
                'at' => $now->format('Y-m-d H:i:s'),
            ];

            echo $candidat['real_name'] . " : " . $candidatScore . "\n";
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
     * This function calculate score of a candidat between two dates
     */
    public function calculateCandidatScoreBetweenDates($candidat, \DateTime $startDate, \DateTime $endDate)
    {
        $predictionTweet = new PredictionTweet();
        $tweetsScore = $predictionTweet->calculateAllTweetsScoreForCandidatBetweenDates($candidat, $startDate, $endDate);
        
        $predictionNews = new PredictionNews();
        $newsScore = $predictionNews->calculateAllNewsScoreForCandidatBetweenDates($candidat, $startDate, $endDate);

        $predictionCommunity = new PredictionCommunity();
        $meanCommunityScoreAsPercent = $predictionCommunity->calculateMeanCandidatCommunityAsPercentBetweenTwoDate($candidat, $startDate, $endDate);

        $predictionSurvey = new PredictionSurvey();
        $meanSurveyScore = $predictionSurvey->calculateMeanCandidatSurveyScoreAsPercentBetweenTwoDate($candidat, $startDate, $endDate);

        $candidatScore = ($tweetsScore * (self::TWEET_VISIBILITY / self::NEWS_VISIBILITY) * (1 + self::TWEET_CONFIDENCE)) + ($newsScore * (1 + self::NEWS_CONFIDENCE));
        $candidatScore *= 1 + ($meanCommunityScoreAsPercent * self::COMMUNITY_PERCENT_COEF);

        $candidatScore *= 1 + $meanSurveyScore;

        return $candidatScore;
    }

}
