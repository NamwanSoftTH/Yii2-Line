# Yii2-SmsGateway

## Installation

```
composer require namwansoft/yii2-sms-gateway "@dev"
```

## Usage

#### Login

```php

$Login = new \namwansoft\Line\Login($clientId, $clientSecret);

$Line->getLoginUrl();

$Line->getToken(['code' => $code]);

$Line->getProfile();

```
