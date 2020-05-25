<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test design config indexer model
 */
namespace Magento\Theme\Test\Unit\Model\Indexer\Design;

use Magento\Framework\Data\Collection;
use Magento\Framework\Indexer\FieldsetInterface;
use Magento\Framework\Indexer\FieldsetPool;
use Magento\Framework\Indexer\HandlerInterface;
use Magento\Framework\Indexer\HandlerPool;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Indexer\SaveHandlerFactory;
use Magento\Framework\Indexer\StructureFactory;
use Magento\Theme\Model\Indexer\Design\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /** @var Config */
    protected $model;

    protected function setUp(): void
    {
        $indexerStructure = $this->getMockBuilder(IndexStructureInterface::class)
            ->getMockForAbstractClass();
        $structureFactory = $this->getMockBuilder(StructureFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $structureFactory->expects($this->any())
            ->method('create')
            ->willReturn($indexerStructure);

        $indexer = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();
        $saveHandlerFactory = $this->getMockBuilder(SaveHandlerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $saveHandlerFactory->expects($this->any())
            ->method('create')
            ->willReturn($indexer);

        $indexerFieldset = $this->getMockBuilder(FieldsetInterface::class)
            ->getMockForAbstractClass();
        $indexerFieldset->expects($this->any())
            ->method('addDynamicData')
            ->willReturnArgument(0);
        $fieldsetPool = $this->getMockBuilder(FieldsetPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fieldsetPool->expects($this->any())
            ->method('get')
            ->willReturn($indexerFieldset);

        $indexerHandler = $this->getMockBuilder(HandlerInterface::class)
            ->getMockForAbstractClass();
        $handlerPool = $this->getMockBuilder(HandlerPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handlerPool->expects($this->any())
            ->method('get')
            ->willReturn($indexerHandler);

        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionFactory =
            $this->getMockBuilder(\Magento\Theme\Model\ResourceModel\Design\Config\Scope\CollectionFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $collectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($collection);

        $this->model = new Config(
            $structureFactory,
            $saveHandlerFactory,
            $fieldsetPool,
            $handlerPool,
            $collectionFactory,
            [
                'fieldsets' => ['test_fieldset' => [
                    'fields' => [
                        'first_field' => [
                            'name' => 'firstField',
                            'origin' => null,
                            'type' => 'filterable',
                            'handler' => null,
                        ],
                        'second_field' => [
                            'name' => 'secondField',
                            'origin' => null,
                            'type' => 'searchable',
                            'handler' => null,
                        ],
                    ],
                    'provider' => $indexerFieldset,
                ]
                ],
                'saveHandler' => 'saveHandlerClass',
                'structure' => 'structureClass',
            ]
        );
    }

    public function testExecuteFull()
    {
        $result = $this->model->executeFull();
        $this->assertNull($result);
    }
}
