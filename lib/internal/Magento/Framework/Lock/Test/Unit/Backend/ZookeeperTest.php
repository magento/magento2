<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock\Test\Unit\Backend;

use Magento\Framework\Lock\Backend\Zookeeper as ZookeeperProvider;
use PHPUnit\Framework\TestCase;

class ZookeeperTest extends TestCase
{
    /**
     * @var ZookeeperProvider
     */
    private $zookeeperProvider;

    /**
     * @var string
     */
    private $host = 'localhost:123';

    /**
     * @var string
     */
    private $path = '/some/path';

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        if (!extension_loaded('zookeeper')) {
            $this->markTestSkipped('Test was skipped because php extension Zookeeper is not installed.');
        }
    }

    /**
     * @return void
     */
    public function testConstructionWithPathException()
    {
        $this->expectException('Magento\Framework\Exception\RuntimeException');
        $this->expectExceptionMessage('The path needs to be a non-empty string.');
        $this->zookeeperProvider = new ZookeeperProvider($this->host, '');
    }

    /**
     * @return void
     */
    public function testConstructionWithHostException()
    {
        $this->expectException('Magento\Framework\Exception\RuntimeException');
        $this->expectExceptionMessage('The host needs to be a non-empty string.');
        $this->zookeeperProvider = new ZookeeperProvider('', $this->path);
    }

    /**
     * @return void
     */
    public function testConstructionWithoutException()
    {
        $this->zookeeperProvider = new ZookeeperProvider($this->host, $this->path);
    }
}
