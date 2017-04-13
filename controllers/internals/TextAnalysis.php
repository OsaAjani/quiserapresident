<?php
namespace controllers\internals;

class TextAnalysis extends \Controller
{

    /**
     * This function check how a text is about
     * @param array $peoples : The peoples to search for ['people1' => ['keywords' => ['coef1' => ['keyword1', 'keyword2', ...], 'coef2' => ['keyword3', ...]]], 'people2' => ...]
     * @param string $text : The text to analyse
     * @return mixed : False if its about nobody, else, an array with people on key and percentage revelance in value
     */
    public static function whoIsAbout($peoples, $text)
    {
        //sanitize the text
        $text = mb_strtolower(self::removeAccent($text));

        //Foreach peoples, we are going to search for number of keyword that appear in the text
        $totalCount = 0;
        $peoplesCount = [];

        foreach ($peoples as $peopleKey => $people)
        {
            //If people not already exist, create it
            if (!isset($peoplesCount[$peopleKey]))
            {
                $peoplesCount[$peopleKey] = 0;
            }

            //Foreach keywords, we are searching for
            foreach ($people['keywords'] as $coef => $keywords)
            {
                //Search the number of occurence for each of thoses keywords in the text, and add the number * the coef to the count for this people
                foreach ($keywords as $keyword)
                {
                    //Sanitize keyword
                    $keyword = mb_strtolower(self::removeAccent($keyword));
                    $keyword = preg_quote($keyword, '#');

                    //Generate regex on run it
                    $matches = [];
                    $regex = '#(^' . $keyword . '[ ,.:;]|[ ,.:;]' . $keyword . '[ ,.:;]|[ ,.:;]' . $keyword . '$)#u';
                    preg_match_all($regex, $text, $matches);

                    //Count result
                    $keywordCount = count($matches[0]) * floatval($coef);

                    $peoplesCount[$peopleKey] += $keywordCount;
                    $totalCount += $keywordCount;
                }
            }
        }

        //If the text correspond to nobody, return false
        if (!$totalCount)
        {
            $totalCount = 1;
        }

        //Transform the score to be a percentage
        foreach ($peoplesCount as $peopleKey => $peopleCount)
        {
            $peoplesCount[$peopleKey] = $peopleCount / $totalCount;
        }

        arsort($peoplesCount);

        return $peoplesCount;
    }

    /**
     * This function remove accent
     * @param string $text : The text to remove accents from
     * @return string : The text without the accents
     */
    public static function removeAccent($text)
    {
        $a = array('À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','ÿ','Ā','ā','Ă','ă','Ą','ą','Ć','ć','Ĉ','ĉ','Ċ','ċ','Č','č','Ď','ď','Đ','đ','Ē','ē','Ĕ','ĕ','Ė','ė','Ę','ę','Ě','ě','Ĝ','ĝ','Ğ','ğ','Ġ','ġ','Ģ','ģ','Ĥ','ĥ','Ħ','ħ','Ĩ','ĩ','Ī','ī','Ĭ','ĭ','Į','į','İ','ı','Ĳ','ĳ','Ĵ','ĵ','Ķ','ķ','Ĺ','ĺ','Ļ','ļ','Ľ','ľ','Ŀ','ŀ','Ł','ł','Ń','ń','Ņ','ņ','Ň','ň','ŉ','Ō','ō','Ŏ','ŏ','Ő','ő','Œ','œ','Ŕ','ŕ','Ŗ','ŗ','Ř','ř','Ś','ś','Ŝ','ŝ','Ş','ş','Š','š','Ţ','ţ','Ť','ť','Ŧ','ŧ','Ũ','ũ','Ū','ū','Ŭ','ŭ','Ů','ů','Ű','ű','Ų','ų','Ŵ','ŵ','Ŷ','ŷ','Ÿ','Ź','ź','Ż','ż','Ž','ž','ſ','ƒ','Ơ','ơ','Ư','ư','Ǎ','ǎ','Ǐ','ǐ','Ǒ','ǒ','Ǔ','ǔ','Ǖ','ǖ','Ǘ','ǘ','Ǚ','ǚ','Ǜ','ǜ','Ǻ','ǻ','Ǽ','ǽ','Ǿ','ǿ');
        $b = array('A','A','A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','U','U','U','U','Y','s','a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y','A','a','A','a','A','a','C','c','C','c','C','c','C','c','D','d','D','d','E','e','E','e','E','e','E','e','E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I','i','I','i','I','i','I','i','IJ','ij','J','j','K','k','L','l','L','l','L','l','L','l','l','l','N','n','N','n','N','n','n','O','o','O','o','O','o','OE','oe','R','r','R','r','R','r','S','s','S','s','S','s','S','s','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u','U','u','W','w','Y','y','Y','Z','z','Z','z','Z','z','s','f','O','o','U','u','A','a','I','i','O','o','U','u','U','u','U','u','U','u','U','u','A','a','AE','ae','O','o');

        return str_replace($a, $b, $text);
    }
}
