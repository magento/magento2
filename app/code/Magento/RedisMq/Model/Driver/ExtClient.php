<?php
declare(strict_types=1);

namespace Magento\RedisMq\Model\Driver;

class ExtClient implements RedisClientInterface
{
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var array
     */
    private $config;

    /**
     * @see https://github.com/phpredis/phpredis#parameters
     */
    public function __construct(array $config)
    {
        if (false == class_exists(\Redis::class)) {
            throw new \LogicException('You must install the redis extension to use phpredis');
        }

        $this->config = $config;
    }

    public function eval(string $script, array $keys = [], array $args = [])
    {
        try {
            return $this->redis->eval($script, array_merge($keys, $args), count($keys));
        } catch (\RedisException $e) {
            throw new ServerException('eval command has failed', 0, $e);
        }
    }

    public function zadd(string $key, string $value, float $score): int
    {
        try {
            return $this->redis->zAdd($key, $score, $value);
        } catch (\RedisException $e) {
            throw new ServerException('zadd command has failed', 0, $e);
        }
    }

    public function zrem(string $key, string $value): int
    {
        try {
            return $this->redis->zRem($key, $value);
        } catch (\RedisException $e) {
            throw new ServerException('zrem command has failed', 0, $e);
        }
    }

    public function lpush(string $key, string $value): int
    {
        try {
            return $this->redis->lPush($key, $value);
        } catch (\RedisException $e) {
            throw new ServerException('lpush command has failed', 0, $e);
        }
    }

    public function brpop(array $keys, int $timeout): ?RedisResult
    {
        try {
            if ($result = $this->redis->brPop($keys, $timeout)) {
                return new RedisResult($result[0], $result[1]);
            }

            return null;
        } catch (\RedisException $e) {
            throw new ServerException('brpop command has failed', 0, $e);
        }
    }

    public function rpop(string $key): ?RedisResult
    {
        try {
            if ($message = $this->redis->rPop($key)) {
                return new RedisResult($key, $message);
            }

            return null;
        } catch (\RedisException $e) {
            throw new ServerException('rpop command has failed', 0, $e);
        }
    }

    private function connect(): void
    {
        if ($this->redis) {
            return;
        }

        $supportedSchemes = ['redis', 'tcp', 'unix'];
        if (false == in_array($this->config['scheme'], $supportedSchemes, true)) {
            throw new \LogicException(sprintf(
                'The given scheme protocol "%s" is not supported by php extension. It must be one of "%s"',
                $this->config['scheme'],
                implode('", "', $supportedSchemes)
            ));
        }

        $this->redis = new \Redis();

        $connectionMethod = $this->config['persistent'] ? 'pconnect' : 'connect';

        $result = call_user_func(
            [$this->redis, $connectionMethod],
            'unix' === $this->config['scheme'] ? $this->config['path'] : $this->config['host'],
            $this->config['port'],
            $this->config['timeout'],
            $this->config['persistent'] ? ($this->config['phpredis_persistent_id'] ?? null) : null,
            $this->config['phpredis_retry_interval'] ?? null,
            $this->config['read_write_timeout']
        );

        if (false == $result) {
            throw new ServerException('Failed to connect.');
        }

        if ($this->config['password']) {
            $this->redis->auth($this->config['password']);
        }

        if (null !== $this->config['database']) {
            $this->redis->select($this->config['database']);
        }
    }

    public function del(string $key): void
    {
        $this->redis->del($key);
    }

    public function __destruct()
    {
        if ($this->redis) {
            $this->redis->close();
        }
    }
}
