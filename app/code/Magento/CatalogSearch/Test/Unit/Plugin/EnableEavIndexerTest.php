<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Plugin;

use Magento\CatalogSearch\Plugin\EnableEavIndexer;
use Magento\Config\Model\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated Implementation class was replaced
 * @see \Magento\ElasticSearch
 */
class EnableEavIndexerTest extends TestCase
{
    /**
     * @var EnableEavIndexer
     */
    private $model;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'setData'])
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            EnableEavIndexer::class
        );
    }

    /**
     * Test with other search engine (not MySQL) selected in config
     *
     * @return void
     */
    public function testBeforeSave()
    {
        $this->config->expects($this->once())->method('getData')->willReturn('elasticsearch');
        $this->config->expects($this->never())->method('setData')->willReturnSelf();

        $this->model->beforeSave($this->config);
    }

    /**
     * Test with MySQL search engine selected in config
     *
     * @return void
     */
    public function testBeforeSaveMysqlSearchEngine()
    {
        $this->config->expects($this->at(0))->method('getData')->willReturn('mysql');
        $this->config->expects($this->at(1))->method('getData')->willReturn([]);
        $this->config->expects($this->once())->method('setData')->willReturnSelf();

        $this->model->beforeSave($this->config);
    }
}
