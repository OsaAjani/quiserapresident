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

}
