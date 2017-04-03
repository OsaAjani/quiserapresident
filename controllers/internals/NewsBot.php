<?php
namespace controllers\internals;

class NewsBot extends \Controller
{
    private $queueId = 64959;
    private $queueMsgType = 52;

    /**
     * This function create bot for searching new Articles for our targeteds medias
     */
    public function searchForNewNews()
    {
    	$mediasRss = ['http://www.lemonde.fr/politique/rss_full.xml'];

    	$bdd = \Model::connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
		$model = new \Model($bdd);

    	foreach ($mediasRss as $media) {

    		$lastAnalyse = $model->getFromTableWhere('news', array(), 'at', true);
			if (!$lastAnalyse) {
				$lastAnalyseDate = new \DateTime("2017-01-01");
			} else {
				$lastAnalyseDate = \DateTime::createfromformat("Y-m-d H:i:s", $lastAnalyse[0]['at']);
			}

			$rss = (array) simplexml_load_file($media);

    		foreach ($rss["channel"]->item as $article) {

    			//link, title, description, pubDate, guid
    			$dateArticle = \DateTime::createfromformat("D, d M Y H:i:s P", $article->pubDate);
    			if($dateArticle->format("Ymd") > $lastAnalyseDate->format('Ymd')) {
    				echo $article->title . "\n";
    				echo $article->pubDate;
    				echo "\n\n";
    			}
    		}
    	}
    }

}
