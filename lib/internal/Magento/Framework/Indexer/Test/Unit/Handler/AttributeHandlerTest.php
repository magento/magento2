<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

// @codingStandardsIgnoreStart
namespace Magento\Framework\Indexer\Handler;

/**
 * Mock method for built-in function method_exists.
 *
 * @param mixed $object
 * @param string $method_name
 * @return bool
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function method_exists($object, $method_name)
{
    return \Magento\Framework\Indexer\Test\Unit\Handler\AttributeHandlerTest::$methodExits;
}
// codingStandardsIgnoreEnd

namespace Magento\Framework\Indexer\Test\Unit\Handler;

use Magento\Framework\Indexer\Handler\AttributeHandler;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;

/**
 * Unit test for Magento\Framework\Indexer\Handler\AttributeHandler.
 */
class AttributeHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Static field responsible for mocking built-in method_exists function result.
     *
     * @var bool
     */
    public static $methodExits = false;

    /**
     * @var SourceProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $source;

    /**
     * @var AttributeHandler
     */
    private $subject;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->source = $this->getMockBuilder(SourceProviderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['joinAttribute'])
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);

        $this->subject = $objectManager->getObject(AttributeHandler::class);
    }

    public function testPrepareSqlWithBindAndMissingJoinAttributeMethod()
    {
        $alias = 'e';
        $fieldInfo = [
            'name' => 'is_approved',
            'origin' => 'is_approved',
            'type' => 'searchable',
            'dataType' => 'varchar',
            'entity' => 'customer',
            'bind' => '',
        ];

        self::$methodExits = false;
        $this->source->expects($this->never())->method('joinAttribute');

        $this->subject->prepareSql($this->source, $alias, $fieldInfo);
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
            'bind' => '',
        ];

        self::$methodExits = true;
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
            'bind' => null,
        ];
        $this->source->expects($this->once())
            ->method('addFieldToSelect')
            ->with('is_approved', 'left');

        $this->subject->prepareSql($this->source, $alias, $fieldInfo);
    }
}
