<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

namespace Magento\Framework\Indexer\Test\Unit\Handler;

use Magento\Framework\Indexer\Handler\AttributeHandler;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;

<<<<<<< HEAD
=======
/**
 * Unit test for Magento\Framework\Indexer\Handler\AttributeHandler.
 */
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
=======
    /**
     * @inheritdoc
     */
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    protected function setUp()
    {
        $this->source = $this->getMockBuilder(SourceProviderInterface::class)
            ->disableOriginalConstructor()
<<<<<<< HEAD
=======
            ->setMethods(['joinAttribute'])
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);

<<<<<<< HEAD
        $this->subject = $objectManager->getObject(
            AttributeHandler::class,
            []
        );
    }

    public function testPrepareSql()
=======
        $this->subject = $objectManager->getObject(AttributeHandler::class);
    }

    public function testPrepareSqlWithBindAndExistingJoinAttributeMethod()
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        $alias = 'e';
        $fieldInfo = [
            'name' => 'is_approved',
            'origin' => 'is_approved',
            'type' => 'searchable',
            'dataType' => 'varchar',
            'entity' => 'customer',
<<<<<<< HEAD
            'bind' => null
        ];
        $this->source->expects($this->once())
            ->method('addFieldToSelect')
            ->with('is_approved', 'left')
=======
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
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            ->willReturnSelf();

        $this->subject->prepareSql($this->source, $alias, $fieldInfo);
    }
<<<<<<< HEAD
=======

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
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
}
