<?php

namespace app;

use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\Common\OAuth2TokenBuilder;


class Auth
{
    public function googleAuth() {
        // Generate a refreshable OAuth2 credential for authentication.
        $oAuth2Credential = (new OAuth2TokenBuilder())
            ->fromFile(BASE_PATH . 'adsapi_php.ini')
            ->build();


        // Construct an API session configured from a properties file and the OAuth2
        // credentials above.
        $session = (new AdManagerSessionBuilder())
            ->fromFile(BASE_PATH . 'adsapi_php.ini')
            ->withOAuth2Credential($oAuth2Credential)
            ->withNetworkCode('21707114005')//21707114005//21698410853//112935618//21708480437//71161633//(112935618)
            ->build();

        return $session;
    }
}