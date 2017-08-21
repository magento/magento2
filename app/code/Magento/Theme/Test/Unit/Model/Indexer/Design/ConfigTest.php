<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test design config indexer model
 */
namespace Magento\Theme\Test\Unit\Model\Indexer\Design;

use Magento\Theme\Model\Indexer\Design\Config;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /** @var Config */
    protected $model;

    protected function setUp()
    {
        $indexerStructure = $this->getMockBuilder(\Magento\Framework\Indexer\IndexStructureInterface::class)
            ->getMockForAbstractClass();
        $structureFactory = $this->getMockBuilder(\Magento\Framework\Indexer\StructureFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $structureFactory->expects($this->any())
            ->method('create')
            ->willReturn($indexerStructure);

        $indexer = $this->getMockBuilder(\Magento\Framework\Indexer\SaveHandler\IndexerInterface::class)
            ->getMockForAbstractClass();
        $saveHandlerFactory = $this->getMockBuilder(\Magento\Framework\Indexer\SaveHandlerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $saveHandlerFactory->expects($this->any())
            ->method('create')
            ->willReturn($indexer);

        $indexerFieldset = $this->getMockBuilder(\Magento\Framework\Indexer\FieldsetInterface::class)
            ->getMockForAbstractClass();
        $indexerFieldset->expects($this->any())
            ->method('addDynamicData')
            ->willReturnArgument(0);
        $fieldsetPool = $this->getMockBuilder(\Magento\Framework\Indexer\FieldsetPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fieldsetPool->expects($this->any())
            ->method('get')
            ->willReturn($indexerFieldset);

        $indexerHandler = $this->getMockBuilder(\Magento\Framework\Indexer\HandlerInterface::class)
            ->getMockForAbstractClass();
        $handlerPool = $this->getMockBuilder(\Magento\Framework\Indexer\HandlerPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handlerPool->expects($this->any())
            ->method('get')
            ->willReturn($indexerHandler);

        $collection = $this->getMockBuilder(\Magento\Framework\Data\Collection::class)
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
                'fieldsets' => ['test_fieldset' =>
                    [
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
