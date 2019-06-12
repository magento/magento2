<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
namespace Magento\CatalogSearch\Test\Unit\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

<<<<<<< HEAD
=======
/**
 * @deprecated
 * @see \Magento\ElasticSearch
 */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
class EnableEavIndexerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogSearch\Plugin\EnableEavIndexer
     */
    private $model;

    /**
     * @var \Magento\Config\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

<<<<<<< HEAD
=======
    /**
     * Set up
     *
     * @return void
     */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    protected function setUp()
    {
        $this->config = $this->getMockBuilder(\Magento\Config\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'setData'])
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\CatalogSearch\Plugin\EnableEavIndexer::class
        );
    }

<<<<<<< HEAD
=======
    /**
     * Test with other search engine (not MySQL) selected in config
     *
     * @return void
     */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    public function testBeforeSave()
    {
        $this->config->expects($this->once())->method('getData')->willReturn('elasticsearch');
        $this->config->expects($this->never())->method('setData')->willReturnSelf();

        $this->model->beforeSave($this->config);
    }

<<<<<<< HEAD
=======
    /**
     * Test with MySQL search engine selected in config
     *
     * @return void
     */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    public function testBeforeSaveMysqlSearchEngine()
    {
        $this->config->expects($this->at(0))->method('getData')->willReturn('mysql');
        $this->config->expects($this->at(1))->method('getData')->willReturn([]);
        $this->config->expects($this->once())->method('setData')->willReturnSelf();

        $this->model->beforeSave($this->config);
    }
}
