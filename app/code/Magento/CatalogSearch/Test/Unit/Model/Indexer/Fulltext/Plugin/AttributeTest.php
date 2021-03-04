<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Indexer\Fulltext\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Attribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\ResourceModel\Attribute
     */
    protected $subjectMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Attribute|\PHPUnit\Framework\MockObject\MockObject
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
     * @var \Magento\Framework\Search\Request\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $config;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->subjectMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Attribute::class);
        $this->indexerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Indexer\IndexerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );
        $this->indexerRegistryMock = $this->createPartialMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
            ['get']
        );
        $this->attributeMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            ['dataHasChangedFor', 'isObjectNew', 'getIsSearchable', 'getData']
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
            ->method('getData')
            ->with('is_searchable')
            ->willReturn(true);
        $this->assertNull(
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

    /**
     * Test afterSave with invalidation.
     *
     * @param bool $saveNeedInvalidation
     * @param bool $saveIsNew
     * @dataProvider afterSaveDataProvider
     */
    public function testAfterSaveWithInvalidation(bool $saveNeedInvalidation, bool $saveIsNew)
    {
        $model = $this->objectManager->getObject(
            Attribute::class,
            [
                'indexerRegistry' => $this->indexerRegistryMock,
                'config' => $this->config,
                'saveNeedInvalidation' => $saveNeedInvalidation,
                'saveIsNew' => $saveIsNew,
            ]
        );

        if ($saveNeedInvalidation) {
            $this->indexerMock->expects($this->once())->method('invalidate');
            $this->prepareIndexer();
        }

        if ($saveIsNew || $saveNeedInvalidation) {
            $this->config->expects($this->once())
                ->method('reset');
        }

        $this->assertEquals(
            $this->subjectMock,
            $model->afterSave($this->subjectMock, $this->subjectMock)
        );
    }

    /**
     * @return array
     */
    public function afterSaveDataProvider(): array
    {
        return [
            'save_new_with_invalidation' => ['saveNeedInvalidation' => true, 'isNew' => true],
            'save_new_without_invalidation' => ['saveNeedInvalidation' => false, 'isNew' => true],
            'update_existing_with_inalidation' => ['saveNeedInvalidation' => true, 'isNew' => false],
            'update_existing_without_inalidation' => ['saveNeedInvalidation' => false, 'isNew' => false],
        ];
    }

    public function testBeforeDelete()
    {
        $this->attributeMock->expects($this->once())
            ->method('isObjectNew')
            ->willReturn(false);
        $this->attributeMock->expects($this->once())
            ->method('getIsSearchable')
            ->willReturn(true);
        $this->assertNull(
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
            ->willReturn($this->indexerMock);
    }
}
