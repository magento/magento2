<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);
=======
>>>>>>> upstream/2.2-develop

namespace Magento\Framework\Indexer\Test\Unit\Handler;

use Magento\Framework\Indexer\Handler\AttributeHandler;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;

<<<<<<< HEAD
/**
 * Unit test for Magento\Framework\Indexer\Handler\AttributeHandler.
 */
=======
>>>>>>> upstream/2.2-develop
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

<<<<<<< HEAD
    /**
     * @inheritdoc
     */
=======
>>>>>>> upstream/2.2-develop
    protected function setUp()
    {
        $this->source = $this->getMockBuilder(SourceProviderInterface::class)
            ->disableOriginalConstructor()
<<<<<<< HEAD
            ->setMethods(['joinAttribute'])
=======
>>>>>>> upstream/2.2-develop
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);

<<<<<<< HEAD
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
=======
        $this->subject = $objectManager->getObject(
            AttributeHandler::class,
            []
        );
    }

    public function testPrepareSql()
>>>>>>> upstream/2.2-develop
    {
        $alias = 'e';
        $fieldInfo = [
            'name' => 'is_approved',
            'origin' => 'is_approved',
            'type' => 'searchable',
            'dataType' => 'varchar',
            'entity' => 'customer',
<<<<<<< HEAD
        ];
        $this->source->expects($this->once())
            ->method('addFieldToSelect')
            ->with('is_approved', 'left');
=======
            'bind' => null
        ];
        $this->source->expects($this->once())
            ->method('addFieldToSelect')
            ->with('is_approved', 'left')
            ->willReturnSelf();
>>>>>>> upstream/2.2-develop

        $this->subject->prepareSql($this->source, $alias, $fieldInfo);
    }
}
