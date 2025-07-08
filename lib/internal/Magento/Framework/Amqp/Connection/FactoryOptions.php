<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Amqp\Connection;

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
     * @var string
     */
    private $virtualHost;

    /**
     * @var bool
     */
    private $sslEnabled = false;

    /**
     * @var array|null
     */
    private $sslOptions;

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     *
     * @return void
     */
    public function setHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @param string $port
     *
     * @return void
     */
    public function setPort(string $port)
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return void
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return void
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getVirtualHost()
    {
        return $this->virtualHost;
    }

    /**
     * @param string|null $virtualHost
     *
     * @return void
     */
    public function setVirtualHost(?string $virtualHost = null)
    {
        $this->virtualHost = $virtualHost;
    }

    /**
     * @return bool
     */
    public function isSslEnabled(): bool
    {
        return $this->sslEnabled;
    }

    /**
     * @param bool $sslEnabled
     *
     * @return void
     */
    public function setSslEnabled(bool $sslEnabled)
    {
        $this->sslEnabled = $sslEnabled;
    }

    /**
     * @return array|null
     */
    public function getSslOptions()
    {
        return $this->sslOptions;
    }

    /**
     * @param array|null $sslOptions
     *
     * @return void
     */
    public function setSslOptions(?array $sslOptions = null)
    {
        $this->sslOptions = $sslOptions;
    }
}
