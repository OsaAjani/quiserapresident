<?php
namespace controllers\internals;

use Abraham\TwitterOAuth\TwitterOAuth;
use Facebook;
use Madcoda\Youtube\Youtube;

class AccountsBot extends \Controller
{
    /**
     * This function run bot for searching all candidats accounts statistics
     */
    public function searchForAccountsStatistics()
    {
        global $candidats;
        global $bdd;
        $model = new \Model($bdd);

        echo "Searching candidats community...\n";

        //Foreach candidat, we are searching for the size of his community
        foreach ($candidats as $name => $candidat)
        {
            echo "    " . $name . "...\n";

            echo "        Twitter... ";
            $twitterFollowers = $this->searchStatisticsTwitter($candidat['accounts']['twitter']);
            echo $twitterFollowers . "\n";

            echo "        Facebook... ";
            $facebookLikes = $this->searchStatisticsFacebook($candidat['accounts']['facebook']);
            echo $facebookLikes . "\n";

            echo "        Youtube... ";
            $youtubeViews = $this->searchStatisticsYoutube($candidat['accounts']['youtube']);
            echo $youtubeViews . "\n";

            echo "    Inserting community into database...";

            //Creating community object and try to insert
            $now = new \DateTime();
            $now = $now->format('Y-m-d H:i:s');

            $communityToInsert = [
                'candidat' => $name,
                'twitter_follower' => $twitterFollowers,
                'facebook_like' => $facebookLikes,
                'youtube_views' => $youtubeViews,
                'at' => $now,
            ];

            if (!$model->insertIntoTable('community', $communityToInsert))
            {
                echo "KO.\n\n";
                continue;
            }

            echo "OK.\n\n";
        }

        echo "...Done.\n";
    }

    /**
     * This function search statistics for Twitter accounts
     * @param array $accounts : Accounts we want to get statistics for format ['coef1' => ['account1', 'account2'], 'coef2' => ['account3', ...], ...]
     * @return mixed : if fail, false, else number of follower
     */
    public function searchStatisticsTwitter($accounts)
    {
        $twitterConnection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, TWITTER_OAUTH_TOKEN, TWITTER_OAUTH_SECRET);

        $followersCount = 0;

        foreach ($accounts as $coef => $accountsList)
        {
            foreach ($accountsList as $account)
            {
                if (!$user = $twitterConnection->get('users/show', ['screen_name' => $account]))
                {
                    return false;
                }

                $followersCount += $user->followers_count * $coef;
            }
        }

        return round($followersCount);
    }

    /**
     * This function search statistics for Facebook accounts
     * @param array $accounts : Accounts we want to get statistics for format ['coef1' => ['account1', 'account2'], 'coef2' => ['account3', ...], ...]
     * @return mixed : if fail, false, else number of follower
     */
    public function searchStatisticsFacebook($accounts)
    {
        $fbApp = new Facebook\FacebookApp(FACEBOOK_APP_ID, FACEBOOK_APP_SECRET);

        $fbConnection = new \Facebook\Facebook([
            'app_id' => FACEBOOK_APP_ID,
            'app_secret' => FACEBOOK_APP_SECRET,
            'default_graph_version' => 'v2.8',
        ]);
    
        $accessToken = $fbApp->getAccessToken();

        $likesCount = 0;

        foreach ($accounts as $coef => $accountsList)
        {
            foreach ($accountsList as $account)
            {
                $response = $fbConnection->get('/' . $account . '/?fields=fan_count', $accessToken);
                $likesCount += $response->getDecodedBody()['fan_count'] * $coef;
            }
        }

        return round($likesCount);
    }

    /**
     * This function search statistics for Youtube accounts
     * @param array $accounts : Accounts we want to get statistics for format ['coef1' => ['account1', 'account2'], 'coef2' => ['account3', ...], ...]
     * @return mixed : if fail, false, else number of follower
     */
    public function searchStatisticsYoutube($accounts)
    {
        $youtubeConnection = new Youtube(array('key' => YOUTUBE_KEY));

        $viewsCount = 0;

        foreach ($accounts as $coef => $accountsList)
        {
            foreach ($accountsList as $account)
            {
                $channelInfo = $youtubeConnection->getChannelById($account);
                $viewsCount += $channelInfo->statistics->viewCount * $coef;
            }
        }

        return round($viewsCount);
    }
}
