<?php

namespace Autowp\ExternalLoginService\Provider;

use J4k\OAuth2\Client\Provider\Vkontakte as VkProvider;
use League\OAuth2\Client\Token\AccessToken;

class Vk extends VkProvider
{
    /**
     * @var string
     */
    private $lang = null;

    public $scopes = [
        'status'
        //'email',
        //'friends',
        //'offline',
        //'photos',
        //'wall',
        //'ads',
        //'audio',
        //'docs',
        //'groups',
        //'market',
        //'messages',
        //'nohttps',
        //'notes',
        //'notifications',
        //'notify',
        //'pages',
        //'stats',
        //'status',
        //'video',
    ];

    public $userFields = [
        //'bdate',
        //'city',
        //'country',
        //'domain',
        'first_name',
        //'friend_status',
        //'has_photo',
        //'home_town',
        'id',
        //'is_friend',
        'last_name',
        //'maiden_name',
        'nickname',
        //'photo_max',
        'photo_max_orig',
        'screen_name',
        //'sex',
        //'about',
        //'activities',
        //'blacklisted',
        //'blacklisted_by_me',
        //'books',
        //'can_post',
        //'can_see_all_posts',
        //'can_see_audio',
        //'can_send_friend_request',
        //'can_write_private_message',
        //'career',
        //'common_count',
        //'connections',
        //'contacts',
        //'crop_photo',
        //'counters',
        //'deactivated',
        //'education',
        //'exports',
        //'followers_count',
        //'games',
        //'has_mobile',
        //'hidden',
        //'interests',
        //'is_favorite',
        //'is_hidden_from_feed',
        //'last_seen',
        //'military',
        //'movies',
        //'occupation',
        //'online',
        //'personal',
        //'photo_100',
        //'photo_200',
        //'photo_200_orig',
        //'photo_400_orig',
        //'photo_50',
        //'photo_id',
        //'quotes',
        //'relation',
        //'relatives',
        //'schools',
        //'site',
        //'status',
        //'timezone',
        //'tv',
        //'universities',
        //'verified',
        //'wall_comments',
    ];

    public function setLang($language)
    {
        $this->lang = (string)$language;

        return $this;
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        $params = [
            'fields'       => $this->userFields,
            'access_token' => $token->getToken(),
            'v'            => $this->version,
            'lang'         => $this->lang
        ];
        $query  = $this->buildQueryString($params);
        $url    = "$this->baseUri/users.get?$query";

        return $url;
    }
}
