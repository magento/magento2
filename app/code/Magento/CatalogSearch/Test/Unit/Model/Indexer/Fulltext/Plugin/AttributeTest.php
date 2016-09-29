<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Fulltext\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Attribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ResourceModel\Attribute
     */
    protected $subjectMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMock;

    /**
     * @var Attribute
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Search\Request\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->subjectMock = $this->getMock(\Magento\Catalog\Model\ResourceModel\Attribute::class, [], [], '', false);
        $this->indexerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Indexer\IndexerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );
        $this->indexerRegistryMock = $this->getMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
            ['get'],
            [],
            '',
            false
        );
        $this->attributeMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            ['dataHasChangedFor', 'isObjectNew', 'getIsSearchable'],
            [],
            '',
            false
        );
        $this->config =  $this->getMockBuilder(\Magento\Framework\Search\Request\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['reset'])
            ->getMock();
        $this->model = $this->objectManager->getObject(
            Attribute::class,
            [
                'indexerRegistry' => $this->indexerRegistryMock,
                'config' => $this->config
            ]
        );
    }

    public function testBeforeSave()
    {
        $this->attributeMock->expects($this->once())
            ->method('isObjectNew')
            ->willReturn(true);
        $this->attributeMock->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('is_searchable')
            ->willReturn(true);
        $this->assertEquals(
            null,
            $this->model->beforeSave($this->subjectMock, $this->attributeMock)
        );
    }

    public function testAfterSaveNoInvalidation()
    {
        $this->assertEquals(
            $this->subjectMock,
            $this->model->afterSave($this->subjectMock, $this->subjectMock)
        );
    }

    public function testAfterSaveWithInvalidation()
    {
        $model = $this->objectManager->getObject(
            Attribute::class,
            [
                'indexerRegistry' => $this->indexerRegistryMock,
                'config' => $this->config,
                'saveNeedInvalidation' => true,
                'saveIsNew' => true
            ]
        );

        $this->indexerMock->expects($this->once())->method('invalidate');
        $this->prepareIndexer();
        $this->config->expects($this->once())
            ->method('reset');

        $this->assertEquals(
            $this->subjectMock,
            $model->afterSave($this->subjectMock, $this->subjectMock)
        );
    }

    public function testBeforeDelete()
    {
        $this->attributeMock->expects($this->once())
            ->method('isObjectNew')
            ->willReturn(false);
        $this->attributeMock->expects($this->once())
            ->method('getIsSearchable')
            ->willReturn(true);
        $this->assertEquals(
            null,
            $this->model->beforeDelete($this->subjectMock, $this->attributeMock)
        );
    }

    public function testAfterDeleteNoInvalidation()
    {
        $this->assertEquals(
            $this->subjectMock,
            $this->model->afterDelete($this->subjectMock, $this->subjectMock)
        );
    }

    public function testAfterDeleteWithInvalidation()
    {
        $model = $this->objectManager->getObject(
            Attribute::class,
            [
                'indexerRegistry' => $this->indexerRegistryMock,
                'config' => $this->config,
                'deleteNeedInvalidation' => true
            ]
        );

        $this->indexerMock->expects($this->once())->method('invalidate');
        $this->prepareIndexer();

        $this->assertEquals(
            $this->subjectMock,
            $model->afterDelete($this->subjectMock, $this->subjectMock)
        );
    }

    private function prepareIndexer()
    {
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)
            ->will($this->returnValue($this->indexerMock));
    }
}
