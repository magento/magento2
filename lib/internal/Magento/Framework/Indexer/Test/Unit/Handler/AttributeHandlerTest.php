<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

namespace Magento\Framework\Indexer\Test\Unit\Handler;

use Magento\Framework\Indexer\Handler\AttributeHandler;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;

<<<<<<< HEAD
=======
/**
 * Unit test for Magento\Framework\Indexer\Handler\AttributeHandler.
 */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    protected function setUp()
    {
        $this->source = $this->getMockBuilder(SourceProviderInterface::class)
            ->disableOriginalConstructor()
<<<<<<< HEAD
=======
            ->setMethods(['joinAttribute'])
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
}
