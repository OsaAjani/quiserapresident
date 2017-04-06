<?php
namespace controllers\internals;

use Sunra\PhpSimple\HtmlDomParser;
use PHPInsight\Sentiment;

class NewsBot extends \Controller
{

    /**
     * This function create bot for searching new Articles for our targeteds medias
     */
    public function searchForNewNews()
    {
        global $candidats;
        $termsToSearch = [];
    	$mediasRss = [
            'Le Monde' => 'http://www.lemonde.fr/politique/rss_full.xml',
            'Liberation' => 'http://rss.liberation.fr/rss/11/',
            'L\'Express' => 'http://www.lexpress.fr/rss/politique.xml',
            'Le Figaro' => 'http://www.lefigaro.fr/rss/figaro_politique.xml',
            '20 Minutes' => 'http://www.20minutes.fr/feeds/rss-politique.xml'
        ];

    	$bdd = \Model::connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
		$model = new \Model($bdd);

        foreach ($candidats as $candidat)
        {
            foreach ($candidat['keywords'] as $keywordsCoef => $keywords)
            {
                $termsToSearch = array_merge($termsToSearch, $keywords);
            }
        }

    	foreach ($mediasRss as $media => $rss) {

    		$lastAnalyse = $model->getFromTableWhere('news', array(), 'at', true);
			if (!$lastAnalyse) {
				$lastAnalyseDate = new \DateTime("2017-01-01");
			} else {
				$lastAnalyseDate = \DateTime::createfromformat("Y-m-d H:i:s", $lastAnalyse[0]['at']);
			}

			$rssContent = (array) simplexml_load_file($rss);

            if ($media == 'Liberation') {
                foreach ($rssContent["entry"] as $news) {
                    $dateArticle = \DateTime::createfromformat('Y-m-d\TH:i:sP', $news->updated);
                    if($dateArticle->format("Ymd") > $lastAnalyseDate->format('Ymd')) {
                        $content = $this->getNewsContent($news->link[0]["href"], $media);

                        if (!$content) {
                            continue;
                        }

                        $article = array("title" => $news->title, "content" => $content, "media" => $media);
                        $this->processingNews($article);
                    }
                }

                continue;
            }

    		foreach ($rssContent["channel"]->item as $news) {

    			//link, title, description, pubDate, guid
    			$dateArticle = \DateTime::createfromformat("D, d M Y H:i:s P", $news->pubDate);
    			if($dateArticle->format("Ymd") > $lastAnalyseDate->format('Ymd')) {
    				$content = $this->getNewsContent($news->link, $media);
                    
                    if (!$content) {
                        continue;
                    }

                    $article = array("title" => $news->title, "content" => $content, "media" => $media);
                    $this->processingNews($article);
    			}
    		}
    	}
    }

    /**
    * This function parse media and extract content of news.
    * @param $news object news from rss
    * @param $media String media
    */
    private function getNewsContent($url, $media)
    {
        //get HTML content of news
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $html = curl_exec($ch);
        curl_close($ch);
        
        $dom = HtmlDomParser::str_get_html($html);
        if ($dom == null) {
            echo $url . "\n";
            return null;
        }

        switch ($media) {
            case 'Le Monde':
                $content = $dom->find('div[id=articleBody]',0);
                break;
            case 'Liberation':
                $content = $dom->find('div[class=article-body]',0);
                break;
            case 'L\'Express':
                $content = $dom->find('div[class=article_container]',0);
                break;
            case 'Le Figaro':
                $content = $dom->find('article',0);
                break;
            case '20 Minutes':
                $content = $dom->find('div[itemprop=articleBody]',0);
                break;
            default:
                break;
        }
        
        //remove balise html from content
        $content = strip_tags($content);
        return $content;
    }

    /**
     * This function processing the news to analyse this quality, sentiment, etc.
     * @param array $news : The news to process array with title and content
     */
    private function processingNews($news)
    {
        global $candidats;

        //Searching who the title of news is about
        $whoIsAbout = TextAnalysis::whoIsAbout($candidats, $news['title']);

        //If we dont know, we search on content
        if ($whoIsAbout[array_keys($whoIsAbout)[0]] == 0 || ($whoIsAbout[array_keys($whoIsAbout)[0]] == $whoIsAbout[array_keys($whoIsAbout)[1]]))
        {
            $whoIsAbout = TextAnalysis::whoIsAbout($candidats, $news['content']);

            //if we dont know, we dont care
            if ($whoIsAbout[array_keys($whoIsAbout)[0]] == 0 || ($whoIsAbout[array_keys($whoIsAbout)[0]] == $whoIsAbout[array_keys($whoIsAbout)[1]]))
            {
                return false;
            }
        }

        reset($whoIsAbout);

        //Analysing news sentiment
        $sentimentAnalyser = new Sentiment(false, 'fr');

        $sentimentScoreTitle = $sentimentAnalyser->score($news['title']);
        $mainSentimentTitle = $sentimentAnalyser->categorise($news['title']);

        $sentimentScoreContent = $sentimentAnalyser->score($news['content']);
        $mainSentimentContent = $sentimentAnalyser->categorise($news['content']);

        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');

        //Create news array to insert
        $newsToInsert = [
            'candidat' => key($whoIsAbout),
            'media' => $news["media"],
            'title' => $news["title"],
            'content' => $news["content"],
            'main_sentiment_title' => $mainSentimentTitle,
            'main_sentiment_value_title' => $sentimentScoreTitle[$mainSentimentTitle],
            'sentiments_value_title' => serialize($sentimentScoreTitle),
            'main_sentiment_content' => $mainSentimentContent,
            'main_sentiment_value_content' => $sentimentScoreContent[$mainSentimentContent],
            'sentiments_value_content' => serialize($sentimentScoreContent),
            'at' => $now,
        ];

        global $bdd;
        $model = new \Model($bdd);
        return $model->insertIntoTable('news', $newsToInsert);
    }

}
