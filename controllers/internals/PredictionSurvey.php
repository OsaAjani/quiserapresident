<?php
namespace controllers\internals;

class PredictionSurvey extends \Controller
{
    /**
     * Return mean candidat survey score as percent beetwen two date
     */
    public function calculateMeanCandidatSurveyScoreAsPercentBetweenTwoDate($candidat, \DateTime $startDate, \DateTime $endDate)
    {
        $daysBetweenDates = new \DatePeriod(
            $startDate,
            new \DateInterval('P1D'),
            $endDate
        );

        $numberOfDay = 0;
        $totalPercent = 0;

        foreach ($daysBetweenDates as $day)
        {
            $candidatSurvey = $this->getCandidatSurveyForDay($candidat, $day);

            if (!$candidatSurvey)
            {
                continue;
            }

            $numberOfDay ++;
            $totalPercent += $candidatSurvey['value'];
        }

        if (!$numberOfDay)
        {
            return 0;
        }

        return $totalPercent / $numberOfDay;
    }

    /**
     * Return the survey score of a candidate for a given day
     */
    public function getCandidatSurveyForDay($candidat, \DateTime $day)
    {
        global $bdd;
        $model = new \Model($bdd);

        $surveys = $model->getFromTableWhere('survey', ['at' => $day->format('Y-m-d'), 'candidat' => $candidat['name']], 'at', true, 1);

        if (!$surveys)
        {
            return false;
        }

        return $surveys[0];
    }
}
