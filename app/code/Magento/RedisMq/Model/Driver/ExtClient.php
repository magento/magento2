<?php
declare(strict_types=1);

namespace Magento\RedisMq\Model\Driver;

use Magento\Framework\App\DeploymentConfig;

class ExtClient implements RedisClientInterface
{
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var DeploymentConfig
     */
    private $config;

    /**
     *
     */
    public function __construct(DeploymentConfig $config)
    {
        if (false == class_exists(\Redis::class)) {
            throw new \LogicException('You must install the redis extension to use phpredis');
        }

        if (version_compare(phpversion('redis'), '4.3.0', '<')) {
            throw new \LogicException('The redis transport requires php-redis 4.3.0 or higher.');
        }

        $this->config = $config;
    }

    public function __call($name, $arguments)
    {
        return $this->getClient()->$name(...$arguments);
    }

    private function getClient(): \Redis
    {
        if (!$this->redis) {
            $config = $this->config->get('queue/redis');
            if (empty($config)) {
                throw new \LogicException('No configuration defined for Redis queue');
            }
            $supportedSchemes = ['redis', 'tcp', 'unix'];
            $scheme = $config['scheme'] ?? 'redis';
            if (false == in_array($scheme, $supportedSchemes, true)) {
                throw new \LogicException(sprintf(
                    'The given scheme protocol "%s" is not supported by php extension. It must be one of "%s"',
                    $config['scheme'],
                    implode('", "', $supportedSchemes)
                ));
            }

            $this->redis = new \Redis();
            $connectionMethod = empty($config['persistent']) ? 'connect' : 'pconnect';

            $result = $this->redis->$connectionMethod(
                'unix' === $scheme ? $config['path'] : $config['host'],
                (int)($config['port'] ?? 6379),
                $config['timeout'] ?? 0,
                !empty($config['persistent']) ? ($config['persistent_id'] ?? null) : null,
                $config['retry_interval'] ?? 0,
                $config['read_write_timeout'] ?? 0.0
            );

            if (false == $result) {
                throw new ServerException('Failed to connect.');
            }

            if (!empty($config['password'])) {
                $this->getClient()->auth($config['password']);
            }

            if (!empty($config['database'])) {
                $this->getClient()->select($this->config['database']);
            }

        }
        return $this->redis;
    }

    /**
     * Handle connection
     */
    public function __destruct()
    {
        if ($this->redis && $this->redis->isConnected() && empty($this->redis->getPersistentID())) {
            // disconnect if connected and not persistent
            $this->redis->close();
        }
    }
}
