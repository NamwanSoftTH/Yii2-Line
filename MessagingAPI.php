<?php

namespace namwansoft\Line;

use Yii;

class MessagingAPI extends \yii\base\Component
{

    private $URL_Api = 'https://api.line.me/v2/bot';
    private $URL_Data = 'https://api-data.line.me/v2/bot';
    private $URL_Liff = 'https://api.line.me/liff/v1/apps/';
    private $URL_Token = 'https://api.line.me/v2/oauth/accessToken';
    private $URL_Token_Verify = 'https://api.line.me/oauth2/v2.1/verify';
    private $accessToken;
    private $Proxy = false;

    public function __construct($accessToken = null)
    {
        parent::__construct();
        $this->accessToken = $accessToken;
    }

    public function BotInfo()
    {
        return $this->cUrl('GET', $this->URL_Api . '/info', null, $this->accessToken);
    }

    public function BotFollowers($date)
    {
        return $this->cUrl('GET', $this->URL_Api . '/insight/followers?date=' . $date, null, $this->accessToken);
    }
    public function BotDeliveries($date)
    {
        return $this->cUrl('GET', $this->URL_Api . '/insight/message/delivery?date=' . $date, null, $this->accessToken);
    }
    public function BotMsgQuota()
    {
        return $this->cUrl('GET', $this->URL_Api . '/message/quota', null, $this->accessToken);
    }
    public function BotMsgQuotaUse()
    {
        return $this->cUrl('GET', $this->URL_Api . '/message/quota/consumption', null, $this->accessToken);
    }

    public function BotWebhook($act, $data = false)
    {
        if ($act == 'get') {
            return $this->cUrl('GET', $this->URL_Api . '/channel/webhook/endpoint', null, $this->accessToken);
        } else if ($act == 'set') {
            return $this->cUrl('PUT', $this->URL_Api . '/channel/webhook/endpoint', json_encode($data), $this->accessToken);
        } else if ($act == 'test') {
            return $this->cUrl('POST', $this->URL_Api . '/channel/webhook/test', $data, $this->accessToken);
        }
    }

    public function SendReply($data, $Token)
    {
        $Curl = $this->cUrl('POST', $this->URL_Api . '/message/reply', $data, $Token);
        $SQL = "INSERT INTO tb__log_oa VALUES (NULL,'" . Yii::$app->Company->id . "','reply','" . $data . "','" . json_encode($Curl) . "','" . time() . "')";
        try {
            if ($Curl['response_code'] !== 200) {
                $DBConn = Yii::$app->getDb();
                $DBConn->createCommand($SQL)->execute();
            }
        } catch (\yii\db\Exception $ex) {}
        return $Curl;
    }

    public function SendPush($data, $Token)
    {
        $tempData = json_decode($data, true);
        $userId = $tempData['to'];
        $reply = Yii::$app->cache->get('replyToken_' . $userId);
        if ($userId && $reply) {
            $newData = str_replace('"to":"' . $userId . '"', '"replyToken":"' . $reply . '"', $data);
            Yii::$app->cache->delete('replyToken_' . $userId);
            return $this->SendReply($newData, $Token);
        }
        $Curl = $this->cUrl('POST', $this->URL_Api . '/message/push', $data, $Token);
        $SQL = "INSERT INTO tb__log_oa VALUES (NULL,'" . Yii::$app->Company->id . "','push','" . $data . "','" . json_encode($Curl) . "','" . time() . "')";
        try {
            if ($Curl['response_code'] !== 200) {
                $DBConn = Yii::$app->getDb();
                $DBConn->createCommand($SQL)->execute();
            }
        } catch (\yii\db\Exception $ex) {}
        return $Curl;
    }
    // public function BotMsgReply($date)
    // {
    //     return $this->cUrl('GET', $this->URL_Api . '/message/delivery/reply?date=' . $date, null, $this->accessToken);
    // }
    // public function BotMsgPush($date)
    // {
    //     return $this->cUrl('GET', $this->URL_Api . '/message/delivery/push?date=' . $date, null, $this->accessToken);
    // }

    /** RichMenu */
    public function RichMenu($richMenuId = false)
    {
        if (!$richMenuId) {
            $Curl = $this->cUrl('GET', $this->URL_Api . '/richmenu/list', null, $this->accessToken);
        } else {
            $Curl = $this->cUrl('GET', $this->URL_Api . '/richmenu/' . $richMenuId, null, $this->accessToken);
        }
        return $Curl;
    }

    public function RichMenuImage($richMenuId)
    {
        return $this->cUrl('GET', $this->URL_Data . '/richmenu/' . $richMenuId . '/content', 'getImg', $this->accessToken, ['Content-Type: image/png']);
    }

    public function RichMenuValidate($data)
    {
        return $this->cUrl('POST', $this->URL_Api . '/richmenu/validate', $data, $this->accessToken);
    }

    public function RichMenuCreate($data)
    {
        return $this->cUrl('POST', $this->URL_Api . '/richmenu', $data, $this->accessToken);
    }

    public function RichMenuUpload($richMenuId, $imagePath, $contentType)
    {
        return $this->cUrl('POST', $this->URL_Data . '/richmenu/' . $richMenuId . '/content', $imagePath, $this->accessToken, $contentType);
    }

    public function RichMenuDelete($richMenuId)
    {
        return $this->cUrl('DELETE', $this->URL_Api . '/richmenu/' . $richMenuId, null, $this->accessToken);
    }

    public function RichMenuDefaultGet()
    {
        return $this->cUrl('GET', $this->URL_Api . '/user/all/richmenu/', null, $this->accessToken);
    }

    public function RichMenuDefaultSet($richMenuId)
    {
        return $this->cUrl('POST', $this->URL_Api . '/user/all/richmenu/' . $richMenuId, null, $this->accessToken);
    }

    public function RichMenuDefaultCancel()
    {
        return $this->cUrl('DELETE', $this->URL_Api . '/user/all/richmenu', null, $this->accessToken);
    }

    public function RichMenuLink($richMenuId, $userId)
    {
        return $this->cUrl('POST', $this->URL_Api . '/user/' . $userId . '/richmenu/' . $richMenuId, null, $this->accessToken);
    }

    public function RichMenuUnLink($userId)
    {
        return $this->cUrl('DELETE', $this->URL_Api . '/user/' . $userId . '/richmenu', null, $this->accessToken);
    }

    public function RichMenuMultiLink($richMenuId, $userIds)
    {
        return $this->cUrl('POST', $this->URL_Api . '/richmenu/bulk/link', ['richMenuId' => $richMenuId, 'userIds' => $userIds], $this->accessToken);
    }

    public function RichMenuMultiUnLink($userIds)
    {
        return $this->cUrl('DELETE', $this->URL_Api . '/richmenu/bulk/unlink', ['userIds' => $userIds], $this->accessToken);
    }

    public function RichMenuReplace($richMenuIdOld, $richMenuIdNew)
    {
        return $this->cUrl('POST', $this->URL_Api . '/richmenu/batch', '{"operations":[{"type":"link","from":"' . $richMenuIdOld . '","to":"' . $richMenuIdNew . '"}]}', $this->accessToken);
    }

    public function RichMenuReplaceStatus($reqId)
    {
        return $this->cUrl('GET', $this->URL_Api . '/richmenu/progress/batch?requestId=' . $reqId, null, $this->accessToken);
    }

    public function RichMenuUser($userId)
    {
        return $this->cUrl('GET', $this->URL_Api . '/user/' . $userId . '/richmenu', null, $this->accessToken);
    }

    /** RichMenu */

    /** Not Edit */
    public function LiffToken($id, $secret)
    {
        $data = 'grant_type=client_credentials&client_id=' . $id . '&client_secret=' . $secret;
        $Curl = $this->cUrl('POST', $this->URL_Token, $data);
        return $Curl['response']['access_token'];
    }

    public function LiffUpdate($liffId, $data, $Token)
    {
        $Curl = $this->cUrl('PUT', $this->URL_Liff . $liffId, $data, $Token);
        return $Curl;
    }

    public function LiffTokenVerify($client_id, $Token)
    {
        $Curl = $this->cUrl('GET', $this->URL_Token_Verify . $Token);
        if ($Curl['response']['client_id'] !== $client_id) {
            return false;
        }
        return true;
    }
    /** Not Edit */

    /**
     * $host = ip:port
     * $auth = user:pass
     */
    public function setProxy($host = false, $auth = false)
    {
        $this->Proxy = (!$host || !$auth) ? false : (object) ['host' => $host, 'auth' => $auth];
    }

    private function cUrl($method, $url, $data = '', $Token = '', $File = false)
    {
        $arHead = ['Authorization: Bearer ' . $Token];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        if ($Token) {
            if ($data == 'getImg') {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($arHead, $File));
            } else if (!$File) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($arHead, ['Content-Type: application/json']));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                $CData = file_get_contents($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $CData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($arHead, ['Content-Type: ' . $File, 'Content-Length: ' . strlen($CData)]));
            }
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        if ($this->Proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->Proxy->host);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->Proxy->auth);
        }
        $response = curl_exec($ch);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $header = $this->headersToArray($header);
        $response = substr($response, $header_size);
        curl_close($ch);
        if ($data == 'getImg') {
            return ['response_code' => $response_code, 'response' => 'data:image/png;base64,' . base64_encode($response)];
        }
        return ['response_code' => $response_code, 'response' => json_decode($response, true), 'response_header' => $header];
    }

    private function headersToArray($str)
    {
        $headers = [];
        $headersTmpArray = explode("\r\n", $str);
        for ($i = 0; $i < count($headersTmpArray); ++$i) {
            if (strlen($headersTmpArray[$i]) > 0) {
                if (strpos($headersTmpArray[$i], ":")) {
                    $headerName = substr($headersTmpArray[$i], 0, strpos($headersTmpArray[$i], ":"));
                    $headerValue = substr($headersTmpArray[$i], strpos($headersTmpArray[$i], ":") + 1);
                    $headers[$headerName] = $headerValue;
                }
            }
        }
        return $headers;
    }

}
