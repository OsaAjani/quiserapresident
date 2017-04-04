<?php
namespace controllers\internals;

use Sunra\PhpSimple\HtmlDomParser;

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
                    }
                }
                continue;
            }

    		foreach ($rssContent["channel"]->item as $news) {

    			//link, title, description, pubDate, guid
    			$dateArticle = \DateTime::createfromformat("D, d M Y H:i:s P", $news->pubDate);
    			if($dateArticle->format("Ymd") > $lastAnalyseDate->format('Ymd')) {
    				$content = $this->getNewsContent($news->link, $media);
    			}
    		}
    	}
    }

    /**
    * This function parse media and extract content of news.
    * @param $news object news from rss
    * @param $media String media
    */
    public function getNewsContent($url, $media)
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

}
