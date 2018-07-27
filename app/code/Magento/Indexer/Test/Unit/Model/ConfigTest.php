<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\Config
     */
    protected $model;

    /**
     * @var \Magento\Indexer\Model\Config\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * Set up test
     */
    protected function setUp()
    {
        $this->configMock = $this->getMock(
            '\Magento\Indexer\Model\Config\Data',
            [],
            [],
            '',
            false
        );

        $this->model = new \Magento\Indexer\Model\Config(
            $this->configMock
        );
    }

    public function testGetIndexers()
    {
        $this->configMock->expects($this->once())->method('get')->with()->willReturnSelf();
        $this->model->getIndexers();
    }

    public function testGetIndexer()
    {
        $indexerId = 1;
        $this->configMock->expects($this->once())->method('get')->with($indexerId)->willReturnSelf();
        $this->model->getIndexer($indexerId);
    }
}
