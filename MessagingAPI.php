<?php

namespace namwansoft\Line;

use Yii;

class MessagingAPI extends \yii\base\Component
{

    private $URL_Api = 'https://api.line.me/v2/bot';
    private $URL_Data = 'https://api-data.line.me/v2/bot';
    private $URL_Liff = 'https://api.line.me/liff/v1/apps/';
    private $URL_Token_url = 'https://api.line.me/v2/oauth/accessToken';
    private $URL_Token_verify = 'https://api.line.me/oauth2/v2.1/verify';
    private $accessToken;

    public function __construct($accessToken = null)
    {
        parent::__construct();
        $this->accessToken = $accessToken;
    }

}
