<?php

namespace Autowp\ExternalLoginService\Provider;

use League\OAuth2\Client\Provider\Facebook as LeagueFacebookProvider;
use League\OAuth2\Client\Provider\AppSecretProof;
use League\OAuth2\Client\Token\AccessToken;

class Facebook extends LeagueFacebookProvider
{
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        $fields = implode(',', [
            'id', 'name', 'first_name', 'last_name',
            'email', 'hometown', 'bio', 'picture.type(large){url,is_silhouette}',
            'cover{source}', 'gender', 'locale', 'link', 'timezone', 'birthday'
        ]);
        $appSecretProof = AppSecretProof::create($this->clientSecret, $token->getToken());

        return static::BASE_GRAPH_URL.$this->graphApiVersion.'/me?fields='.$fields
                        .'&access_token='.$token.'&appsecret_proof='.$appSecretProof;
    }

    public function getDefaultScopes()
    {
        return ['public_profile', 'email', 'user_hometown'];
    }
}
