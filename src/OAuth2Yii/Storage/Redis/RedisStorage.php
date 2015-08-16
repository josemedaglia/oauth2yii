<?php

namespace OAuth2Yii\Storage\Redis;

use \Yii as Yii;
use \CException as CException;

/**
 * Base class for Redis based server storages
 *
 */
abstract class RedisStorage extends \OAuth2Yii\Storage\Storage {

    private $cache;

    /* The redis client */
    protected $redis;

    /* Configuration array */
    protected $config;

    public function __construct(\OAuth2Yii\Component\ServerComponent $server, $redis = null)
    {
        parent::__construct($server);

        //Default to localhost
        if ($redis == null) {
            $redis = [
                'host' => '127.0.0.1',
                'port' => 6379,
                'database' => 1
            ];
        }
        $this->redis = new \Predis\Client($redis);
        
        $this->config = array(
            'client_key' => 'oauth_clients:',
            'access_token_key' => 'oauth_access_tokens:',
            'refresh_token_key' => 'oauth_refresh_tokens:',
            'code_key' => 'oauth_authorization_codes:',
            'user_key' => 'oauth_users:',
            'jwt_key' => 'oauth_jwt:',
            'scope_key' => 'oauth_scopes:',
        );
    }

    /**
     * @return \Predis\Client to use for this storage
     */
    public function getRedis()
    {
        return $this->redis;
    }

    protected function getValue($key)
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        $value = $this->redis->get($key);
        if (isset($value)) {
            return json_decode($value, true);
        } else {
            return false;
        }
    }

    protected function setValue($key, $value, $expire = 0)
    {
        $this->cache[$key] = $value;
        $str = json_encode($value);
        if ($expire > 0) {
            $seconds = $expire - time();
            $ret = $this->redis->setex($key, $seconds, $str);
        } else {
            $ret = $this->redis->set($key, $str);
        }

        // check that the key was set properly
        // if this fails, an exception will usually thrown, so this step isn't strictly necessary
        return is_bool($ret) ? $ret : $ret->getPayload() == 'OK';
    }

    protected function expireValue($key)
    {
        unset($this->cache[$key]);

        return $this->redis->del($key);
    }

}
