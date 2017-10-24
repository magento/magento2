<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogWidget\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Unserialize\SecureUnserializer;
use Magento\Framework\ObjectManagerInterface;

class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogWidget\Model\Rule
     */
    protected $rule;

    /**
     * @var \Magento\CatalogWidget\Model\Rule\Condition\CombineFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $combineFactory;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var SecureUnserializer
     */
    private $unserialize;

    protected function setUp()
    {
        $this->combineFactory = $this->getMockBuilder(\Magento\CatalogWidget\Model\Rule\Condition\CombineFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->prepareObjectManager();

        $this->rule = $this->objectManagerHelper->getObject(
            \Magento\CatalogWidget\Model\Rule::class,
            [
                'conditionsFactory' => $this->combineFactory
            ]
        );
    }

    public function testGetConditionsInstance()
    {
        $condition = $this->getMockBuilder(\Magento\CatalogWidget\Model\Rule\Condition\Combine::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->combineFactory->expects($this->once())->method('create')->will($this->returnValue($condition));
        $this->assertSame($condition, $this->rule->getConditionsInstance());
    }

    public function testGetActionsInstance()
    {
        $this->assertNull($this->rule->getActionsInstance());
    }

    /**
     * Prepares ObjectManager mock.
     *
     * @return void
     */
    private function prepareObjectManager()
    {
        $objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->unserialize =  $this->getMockBuilder(SecureUnserializer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock->expects($this->any())->method('get')->willReturn(
            [SecureUnserializer::class, $this->unserialize]
        );

        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $reflectionClass = new \ReflectionClass(\Magento\Framework\App\ObjectManager::class);
        $reflectionProperty = $reflectionClass->getProperty('_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, null);
    }
}
