<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer\Test\Unit\Handler;

use Magento\Framework\Indexer\Handler\AttributeHandler;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;

class AttributeHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SourceProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $source;

    /**
     * @var AttributeHandler
     */
    private $subject;

    protected function setUp()
    {
        $this->source = $this->getMockBuilder(SourceProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);

        $this->subject = $objectManager->getObject(
            AttributeHandler::class,
            []
        );
    }

    public function testPrepareSql()
    {
        $alias = 'e';
        $fieldInfo = [
            'name' => 'is_approved',
            'origin' => 'is_approved',
            'type' => 'searchable',
            'dataType' => 'varchar',
            'entity' => 'customer',
            'bind' => null
        ];
        $this->source->expects($this->once())
            ->method('addFieldToSelect')
            ->with('is_approved', 'left')
            ->willReturnSelf();

        $this->subject->prepareSql($this->source, $alias, $fieldInfo);
    }
}
