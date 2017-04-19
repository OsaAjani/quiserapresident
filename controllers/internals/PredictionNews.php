<?php
namespace controllers\internals;

class PredictionNews extends \Controller
{
    //Const for news score calculation
    
    //titles score
    const NEWS_SCORE_COEF_TITLE = 5;
    const NEWS_SCORE_COEF_MAIN_SENTIMENT_TITLE_POS = 1;
    const NEWS_SCORE_COEF_MAIN_SENTIMENT_TITLE_NEG = 0;
    const NEWS_SCORE_COEF_MAIN_SENTIMENT_TITLE_NEU = 0.2;

    //content score
    const NEWS_SCORE_COEF_CONTENT = 1;
    const NEWS_SCORE_COEF_MAIN_SENTIMENT_CONTENT_POS = 1;
    const NEWS_SCORE_COEF_MAIN_SENTIMENT_CONTENT_NEG = 0;
    const NEWS_SCORE_COEF_MAIN_SENTIMENT_CONTENT_NEU = 0.2;
    
    /**
     * This function return the news score of a candidat as a percentage (for use in total score calculation algorithm, not a real percentage of candidat score actually)
     */
    public function calculateCandidatNewsScoreBetweenDatesAsPercent ($candidat, \DateTime $startDate, \DateTime $endDate)
    {
        $candidatScore = $this->calculateAllNewsScoreForCandidatBetweenDates($candidat, $startDate, $endDate);
        $totalScore = $this->calculateTotalNewsScoreBetweenDates($startDate, $endDate);

        $news = $this->getAllNewsForCandidatBetweenDates($candidat, $startDate, $endDate);
        $negativesNews = $this->getAllNegativesNewsForCandidatBetweenDates($candidat, $startDate, $endDate);
        $negativesNewsPercent = count($negativesNews) / (count($news) + 1);

        $candidatScore = $candidatScore * (1 - $negativesNewsPercent); //For balancing $candidat score when too much negativ news
        
        $candidatScoreAsPercent = $candidatScore / $totalScore;

        return $candidatScoreAsPercent; 
    }
    /**
     * This function calculate total news score for all candidats between two date
     */
    public function calculateTotalNewsScoreBetweenDates (\DateTime $startDate, \DateTime $endDate)
    {
        global $candidats;

        $totalScore = 0;

        foreach ($candidats as $candidat)
        {
            $totalScore += $this->calculateAllNewsScoreForCandidatBetweenDates ($candidat, $startDate, $endDate);
        }

        return $totalScore;
    }

    /**
     * This function calculate total score of all News for a candidat beetwen two date and calculate score
     */
    public function calculateAllNewsScoreForCandidatBetweenDates ($candidat, \DateTime $startDate, \DateTime $endDate)
    {
        $totalScore = 0;
        $allNews = $this->getAllNewsForCandidatBetweenDates($candidat, $startDate, $endDate);

        foreach ($allNews as $news) {
            $totalScore += $this->calculateNewsScore($news);
        }

        return $totalScore;
    }

    /**
     * This function return all news between two date
     */
    public function getAllNewsForCandidatBetweenDates ($candidat, \DateTime $startDate, \DateTime $endDate)
    {
        global $bdd;
        $model = new \Model($bdd);
        return $model->getFromTableWhere('news', ['>=at' => $startDate->format('Y-m-d H:i:s'), '<=at' => $endDate->format('Y-m-d H:i:s'), 'candidat' => $candidat['name']]);
    }

    /**
     * This function return all negatives news for a candidat between two date
     */
    public function getAllNegativesNewsForCandidatBetweenDates ($candidat, \DateTime $startDate, \DateTime $endDate)
    {
        global $bdd;
        $model = new \Model($bdd);
        return $model->getFromTableWhere('news', ['>=at' => $startDate->format('Y-m-d H:i:s'), '<=at' => $endDate->format('Y-m-d H:i:s'), 'candidat' => $candidat['name'], 'main_sentiment_title' => 'neg']);
    }

    /**
     * This function calculate score of a news
     */
    public function calculateNewsScore ($news)
    {
        $score = 1;

        if ($news['main_sentiment_title'] == 'pos')
        {
            $score += self::NEWS_SCORE_COEF_TITLE * self::NEWS_SCORE_COEF_MAIN_SENTIMENT_TITLE_POS;
        }
        elseif ($news['main_sentiment_title'] == 'neg')
        {
            $score += self::NEWS_SCORE_COEF_TITLE * self::NEWS_SCORE_COEF_MAIN_SENTIMENT_TITLE_NEG;
        }
        elseif ($news['main_sentiment_title'] == 'neu') 
        {
            $score += self::NEWS_SCORE_COEF_TITLE * self::NEWS_SCORE_COEF_MAIN_SENTIMENT_TITLE_NEU;
        }

        if ($news['main_sentiment_content'] == 'pos')
        {
            $score += self::NEWS_SCORE_COEF_CONTENT * self::NEWS_SCORE_COEF_MAIN_SENTIMENT_CONTENT_POS;
        }
        elseif ($news['main_sentiment_content'] == 'neg')
        {
            $score += self::NEWS_SCORE_COEF_CONTENT * self::NEWS_SCORE_COEF_MAIN_SENTIMENT_CONTENT_NEG;
        }
        elseif ($news['main_sentiment_content'] == 'neu') 
        {
            $score += self::NEWS_SCORE_COEF_CONTENT * self::NEWS_SCORE_COEF_MAIN_SENTIMENT_CONTENT_NEU;
        }

        return $score;
    }

}
