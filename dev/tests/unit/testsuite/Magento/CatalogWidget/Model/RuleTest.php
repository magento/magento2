<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\CatalogWidget\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

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
        $this->combineFactory = $this->getMockBuilder('Magento\CatalogWidget\Model\Rule\Condition\CombineFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->rule = $objectManagerHelper->getObject(
            'Magento\CatalogWidget\Model\Rule',
            [
                'conditionsFactory' => $this->combineFactory
            ]
        );
    }

    public function testGetConditionsInstance()
    {
        $condition = $this->getMockBuilder('Magento\CatalogWidget\Model\Rule\Condition\Combine')
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
}
