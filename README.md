# Yii2-Line

## Installation

```
composer require namwansoft/yii2-line "@dev"
```

## Usage

#### Login

```php

$Login = new \namwansoft\Line\Login($clientId, $clientSecret);

$Line->getLoginUrl();

$Line->getToken(['code' => $code]);

$Line->getProfile();

```

#### MessagingAPI

```php

$MsgAPI = new \namwansoft\Line\MessagingAPI($accessToken, $Proxy);



```
