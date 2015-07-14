<?php

namespace OAuth2Yii\Storage\Redis;

use \OAuth2\Storage\AccessTokenInterface;
use \Yii;

/**
 * Server storage for access tokens
 *
 */
class AccessToken extends RedisStorage implements AccessTokenInterface {

    public function getAccessToken($access_token)
    {
        return $this->getValue($this->config['access_token_key'].$access_token);
    }

    public function setAccessToken($access_token, $client_id, $user_id, $expires, $scope = null)
    {
        return $this->setValue(
            $this->config['access_token_key'].$access_token,
            compact('access_token', 'client_id', 'user_id', 'expires', 'scope'),
            $expires
        );
    }

}
