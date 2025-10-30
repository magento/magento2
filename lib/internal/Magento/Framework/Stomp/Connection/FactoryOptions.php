<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Connection;

/**
 * Options a connection will be created according to.
 */
class FactoryOptions
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $port;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var bool
     */
    private $sslEnabled = false;

    /**
     * @var array|null
     */
    private $sslOptions;

    /**
     * Get stomp host
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Set stomp host
     *
     * @param string $host
     * @return void
     */
    public function setHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * Get stomp port
     *
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * Set stomp port
     *
     * @param string $port
     * @return void
     */
    public function setPort(string $port): void
    {
        $this->port = $port;
    }

    /**
     * Get username for stomp
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set username for stomp
     *
     * @param string $username
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * Get stomp password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set stomp password
     *
     * @param string $password
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Check ssl is enabled
     *
     * @return bool
     */
    public function isSslEnabled(): bool
    {
        return $this->sslEnabled;
    }

    /**
     * Set ssl enabled
     *
     * @param bool $sslEnabled
     * @return void
     */
    public function setSslEnabled(bool $sslEnabled): void
    {
        $this->sslEnabled = $sslEnabled;
    }

    /**
     * Get ssl options
     *
     * @return array|null
     */
    public function getSslOptions(): ?array
    {
        return $this->sslOptions;
    }

    /**
     * Set ssl options
     *
     * @param array|null $sslOptions
     * @return void
     */
    public function setSslOptions(?array $sslOptions = null): void
    {
        $this->sslOptions = $sslOptions;
    }
}
