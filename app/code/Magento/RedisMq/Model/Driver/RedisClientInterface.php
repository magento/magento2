<?php
declare(strict_types=1);

namespace Magento\RedisMq\Model\Driver;

interface RedisClientInterface
{
    /**
     * @param string $script
     * @param array  $keys
     * @param array  $args
     *
     * @throws ServerException
     *
     * @return mixed
     */
    public function eval(string $script, array $keys = [], array $args = []);

    /**
     * @param string $key
     * @param string $value
     * @param float  $score
     *
     * @throws ServerException
     *
     * @return int
     */
    public function zadd(string $key, string $value, float $score): int;

    /**
     * @param string $key
     * @param string $value
     *
     * @throws ServerException
     *
     * @return int
     */
    public function zrem(string $key, string $value): int;

    /**
     * @param string $key
     * @param string $value
     *
     * @throws ServerException
     *
     * @return int length of the list
     */
    public function lpush(string $key, string $value): int;

    /**
     * @param string[] $keys
     * @param int      $timeout in seconds
     *
     * @throws ServerException
     *
     * @return RedisResult|null
     */
    public function brpop(array $keys, int $timeout): ?RedisResult;

    /**
     * @param string $key
     *
     * @throws ServerException
     *
     * @return RedisResult|null
     */
    public function rpop(string $key): ?RedisResult;

    /**
     * @param string $key
     *
     * @throws ServerException
     */
    public function del(string $key): void;
}
