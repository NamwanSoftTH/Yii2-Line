# Yii2-SmsGateway

## Installation

```
composer require namwansoft/yii2-sms-gateway "@dev"
```

## Usage

```php

$arSms = (object) [
    'sender_name'   => '',
    'key_api'       => '',
    'key_secret'    => '',
    'OtpKey'        => '',
    'OtpSecret'     => '',
];

```

### Login

```php

$Login = new \namwansoft\Line\Login($$clientId, $clientSecret);

$Line->getLoginUrl();

$Line->getToken(['code' => $code]);

$Line->getProfile();

```
