<?php

namespace OAuth\Common\Storage;

use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Storage\Exception\AuthorizationStateNotFoundException;
use Predis\Client as Predis;

/*
 * Stores a token in a Redis server. Requires the Predis library available at https://github.com/nrk/predis
 */
class Redis implements TokenStorageInterface
{
    /**
     * @var string
     */
    protected $key;

    protected $stateKey;

    /**
     * @var object|\Redis
     */
    protected $redis;

    /**
     * @var object|TokenInterface
     */
    protected $cachedTokens;

    /**
     * @var object
     */
    protected $cachedStates;

    /**
     * @param Predis $redis An instantiated and connected redis client
     * @param string $key The key to store the token under in redis
     * @param string $stateKey The key to store the state under in redis.
     */
    public function __construct(Predis $redis, $key, $stateKey)
    {
        $this->redis = $redis;
        $this->key = $key;
        $this->stateKey = $stateKey;
        $this->cachedTokens = array();
        $this->cachedStates = array();
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveAccessToken($service)
    {
        if (!$this->hasAccessToken($service)) {
            throw new TokenNotFoundException('Token not found in redis');
        }

        if (isset($this->cachedTokens[$service])) {
            return $this->cachedTokens[$service];
        }

        $val = $this->redis->hget($this->key, $service);

        return $this->cachedTokens[$service] = unserialize($val);
    }

    /**
     * {@inheritDoc}
     */
    public function storeAccessToken($service, TokenInterface $token)
    {
        // (over)write the token
        $this->redis->hset($this->key, $service, serialize($token));
        $this->cachedTokens[$service] = $token;

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAccessToken($service)
    {
        if (isset($this->cachedTokens[$service])
            && $this->cachedTokens[$service] instanceof TokenInterface
        ) {
            return true;
        }

        return $this->redis->hexists($this->key, $service);
    }

    /**
     * {@inheritDoc}
     */
    public function clearToken($service)
    {
        $this->redis->hdel($this->key, $service);
        unset($this->cachedTokens[$service]);

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clearAllTokens()
    {
        // memory
        $this->cachedTokens = array();

        // redis
        $keys = $this->redis->hkeys($this->key);
        $me = $this; // 5.3 compat

        // pipeline for performance
        $this->redis->pipeline(
            function ($pipe) use ($keys, $me) {
                foreach ($keys as $k) {
                    $pipe->hdel($me->getKey(), $k);
                }
            }
        );

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveAuthorizationState($service)
    {
        if (!$this->hasAuthorizationState($service)) {
            throw new AuthorizationStateNotFoundException('State not found in redis');
        }

        if (isset($this->cachedStates[$service])) {
            return $this->cachedStates[$service];
        }

        $val = $this->redis->hget($this->stateKey, $service);

        return $this->cachedStates[$service] = $val;
    }

    /**
     * {@inheritDoc}
     */
    public function storeAuthorizationState($service, $state)
    {
        // (over)write the token
        $this->redis->hset($this->stateKey, $service, $state);
        $this->cachedStates[$service] = $state;

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAuthorizationState($service)
    {
        if (isset($this->cachedStates[$service])
            && null !== $this->cachedStates[$service]
        ) {
            return true;
        }

        return $this->redis->hexists($this->stateKey, $service);
    }

    /**
     * {@inheritDoc}
     */
    public function clearAuthorizationState($service)
    {
        $this->redis->hdel($this->stateKey, $service);
        unset($this->cachedStates[$service]);

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clearAllAuthorizationStates()
    {
        // memory
        $this->cachedStates = array();

        // redis
        $keys = $this->redis->hkeys($this->stateKey);
        $me = $this; // 5.3 compat

        // pipeline for performance
        $this->redis->pipeline(
            function ($pipe) use ($keys, $me) {
                foreach ($keys as $k) {
                    $pipe->hdel($me->getKey(), $k);
                }
            }
        );

        // allow chaining
        return $this;
    }

    /**
     * @return Predis $redis
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * @return string $key
     */
    public function getKey()
    {
        return $this->key;
    }
}
