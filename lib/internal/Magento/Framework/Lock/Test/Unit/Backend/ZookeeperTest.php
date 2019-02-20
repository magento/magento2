<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock\Test\Unit\Backend;

use Magento\Framework\Lock\Backend\Zookeeper as ZookeeperProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ZookeeperTest extends TestCase
{
    /**
     * @var \Zookeeper|MockObject
     */
    private $zookeeperMock;

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
    protected function setUp()
    {
        $this->zookeeperProvider = new ZookeeperProvider($this->host, '/some/path/');
    }

    /**
     * @expectedException \Magento\Framework\Exception\RuntimeException
     * @expectedExceptionMessage The path needs to be a non-empty string.
     */
    public function testConstructionWithException()
    {
        $this->zookeeperProvider = new ZookeeperProvider('some host', '');
    }


}
