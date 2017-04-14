<?php
namespace controllers\internals;

class PredictionCommunity extends \Controller
{
    /**
     * Return mean candidat community as percent beetwen two date
     */
    public function calculateMeanCandidatCommunityAsPercentBetweenTwoDate($candidat, \DateTime $startDate, \DateTime $endDate)
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
            $numberOfDay ++;
            $totalPercent += $this->calculateCandidatCommunityAsPercentForDay($candidat, $day);
        }

        return $totalPercent / $numberOfDay;
    }

    /**
     * Return candidat community as percent for a day
     */
    public function calculateCandidatCommunityAsPercentForDay($candidat, \DateTime $day)
    {
        $totalCommunity = 1 + $this->calculateTotalCommunityForDay($day);

        $candidatCommunity = $this->getCandidatCommunityForDay($candidat, $day);

        return ($candidatCommunity / $totalCommunity) * 100;
    }

    /**
     * Return total community for a given day
     */
    public function calculateTotalCommunityForDay(\DateTime $day)
    {
        global $candidats;

        $totalCommunity = 0;

        foreach ($candidats as $candidat)
        {
            $candidatCommunity = $this->getCandidatCommunityForDay($candidat, $day);

            if (!$candidatCommunity)
            {
                continue;
            }

            $totalCommunity += $candidatCommunity['twitter_follower'] + $candidatCommunity['facebook_like'] + $candidatCommunity['youtube_views'];
        }

        return $totalCommunity;
    } 

    /**
     * Return the community of a candidate for a given day
     */
    public function getCandidatCommunityForDay($candidat, \DateTime $day)
    {
        global $bdd;
        $model = new \Model($bdd);

        $communities = $model->getFromTableWhere('community', ['at' => $day->format('Y-m-d'), 'candidat' => $candidat['name']], 'at', true, 1);

        if (!$communities)
        {
            return false;
        }

        return $communities[0];
    }
}
