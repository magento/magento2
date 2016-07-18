<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogWidget\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

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

    protected function setUp()
    {
        $this->combineFactory = $this->getMockBuilder(\Magento\CatalogWidget\Model\Rule\Condition\CombineFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->prepareObjectManager([
            [
                \Magento\Framework\Api\ExtensionAttributesFactory::class,
                $this->getMock(\Magento\Framework\Api\ExtensionAttributesFactory::class, [], [], '', false)
            ],
            [
                \Magento\Framework\Api\AttributeValueFactory::class,
                $this->getMock(\Magento\Framework\Api\AttributeValueFactory::class, [], [], '', false)
            ],
        ]);

        $this->rule = $objectManagerHelper->getObject(
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
     * @param $map
     */
    private function prepareObjectManager($map)
    {
        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())->method('getInstance')->willReturnSelf();
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($map));
        $reflectionClass = new \ReflectionClass(\Magento\Framework\App\ObjectManager::class);
        $reflectionProperty = $reflectionClass->getProperty('_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($objectManagerMock);
    }
}
