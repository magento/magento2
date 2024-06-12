<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer\Test\Unit\Handler;

use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Magento\Framework\Indexer\Handler\AttributeHandler;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Framework\Indexer\Handler\AttributeHandler.
 */
class AttributeHandlerTest extends TestCase
{
    /**
     * @var SourceProviderInterface|MockObject
     */
    private $source;

    /**
     * @var AttributeHandler
     */
    private $subject;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->source = $this->getMockBuilder(SourceProviderInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['joinAttribute'])
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);

        $this->subject = $objectManager->getObject(AttributeHandler::class);
    }

    public function testPrepareSqlWithBindAndExistingJoinAttributeMethod()
    {
        $alias = 'e';
        $fieldInfo = [
            'name' => 'is_approved',
            'origin' => 'is_approved',
            'type' => 'searchable',
            'dataType' => 'varchar',
            'entity' => 'customer',
            'bind' => 'test',
        ];

        $this->source->expects($this->once())
            ->method('joinAttribute')
            ->with(
                $fieldInfo['name'],
                $fieldInfo['entity'] . '/' . $fieldInfo['origin'],
                $fieldInfo['bind'],
                null,
                'left'
            )
            ->willReturnSelf();

        $this->subject->prepareSql($this->source, $alias, $fieldInfo);
    }

    public function testPrepareSqlWithoutBind()
    {
        $alias = 'e';
        $fieldInfo = [
            'name' => 'is_approved',
            'origin' => 'is_approved',
            'type' => 'searchable',
            'dataType' => 'varchar',
            'entity' => 'customer',
        ];
        $this->source->expects($this->once())
            ->method('addFieldToSelect')
            ->with('is_approved', 'left');

        $this->subject->prepareSql($this->source, $alias, $fieldInfo);
    }
}
