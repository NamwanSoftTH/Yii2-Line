<?php

namespace namwansoft\Line;

class Login extends \yii\base\Component
{
    private $clientId;
    private $clientSecret;
    private $Authorization;
    private $EncodeToken;
    private $Proxy = false;

    private $URL_AUTH = 'https://access.line.me/oauth2/v2.1/authorize';
    private $URL_TOKEN = 'https://api.line.me/oauth2/v2.1/token';
    private $URL_STATUS = 'https://api.line.me/friendship/v1/status';
    private $URL_PROFILE = 'https://api.line.me/v2/profile';

    private $payload;

    public function __construct($clientId, $clientSecret = null)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * $host = ip:port
     * $auth = user:pass
     */
    public function setProxy($host = false, $auth = false)
    {
        $this->Proxy = (!$host || !$auth) ? false : (object) ['host' => $host, 'auth' => $auth];
    }

    public function getLoginUrl($args = ['state' => 'default'])
    {
        $params = [
            'response_type' => 'code',
            'scope'         => 'openid profile email',
            'redirect_uri'  => $this->getCurrentUrl(),
            'client_id'     => $this->clientId,
        ];
        $params = array_merge($params, $args);
        return $this->URL_AUTH . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    public function getToken($args = [])
    {
        $params = [
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $this->getCurrentUrl(),
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];
        $params = array_merge($params, $args);
        $cUrl = $this->cUrl('POST', $this->URL_TOKEN, null, http_build_query($params, '', '&', PHP_QUERY_RFC3986), false);
        $this->Authorization = $cUrl->access_token ?? null;
        $this->EncodeToken = $cUrl->id_token ?? null;
        return $cUrl;
    }

    private function getCurrentUrl()
    {
        return "https://" . $_SERVER[HTTP_HOST] . explode('?', $_SERVER['REQUEST_URI'], 2)[0];
    }

    public function getProfile()
    {
        $cUrl = $this->cUrl('POST', $this->URL_PROFILE, null, null);
        $cUrl->dataDecode = $this->getProfileInfo();
        $cUrl->email = $cUrl->dataDecode->email;
        return $cUrl;
    }

    public function getProfileInfo()
    {
        return $this->parseInfo();
    }

    private function cUrl($Method, $Url, $Header = [], $Body = [], $isJson = true)
    {
        $Header = $isJson ? array_merge(['Accept:application/json', 'Content-Type:application/json'], $Header) : $Header;
        $Body = $isJson ? json_encode($Body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $Body;
        if ($this->Authorization) {
            $Header = array_merge(["Authorization: Bearer " . $this->Authorization], $Header ?? []);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $Method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $Body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $Header);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        if ($this->Proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->Proxy->host);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->Proxy->auth);
        }
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res);
    }

    private function base64UrlDecode($data)
    {
        if ($remainder = strlen($data) % 4) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private function parseInfo()
    {
        $this->payload = explode('.', $this->EncodeToken)[1];
        return json_decode($this->base64UrlDecode($this->payload));
    }

}
