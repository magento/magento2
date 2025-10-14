<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Rule\Test\Unit\Model\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Rule\Model\AbstractModel;
use Magento\Rule\Model\Condition\Combine;
use Magento\Rule\Model\Renderer\Conditions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConditionsTest extends TestCase
{
    /**
     * @var Conditions
     */
    protected $conditions;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var AbstractElement|MockObject
     */
    protected $_element;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->conditions = $this->objectManagerHelper->getObject(Conditions::class);
        $this->_element = $this->getMockBuilder(AbstractElement::class)
            ->addMethods(['getRule'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    public function testRender()
    {
        $rule = $this->getMockBuilder(AbstractModel::class)
            ->onlyMethods(['getConditions', '__sleep', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $conditions = $this->createPartialMock(Combine::class, ['asHtmlRecursive']);

        $this->_element->expects($this->any())
            ->method('getRule')
            ->willReturn($rule);

        $rule->expects($this->any())
            ->method('getConditions')
            ->willReturn($conditions);

        $conditions->expects($this->once())
            ->method('asHtmlRecursive')
            ->willReturn('conditions html');

        $this->assertEquals('conditions html', $this->conditions->render($this->_element));
    }
}
