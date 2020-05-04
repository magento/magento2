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
use Magento\Rule\Model\Action\Collection;
use Magento\Rule\Model\Renderer\Actions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ActionsTest extends TestCase
{
    /**
     * @var Actions
     */
    protected $actions;

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
        $this->actions = $this->objectManagerHelper->getObject(Actions::class);
        $this->_element = $this->getMockBuilder(AbstractElement::class)
            ->addMethods(['getRule'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    public function testRender()
    {
        $rule = $this->getMockBuilder(AbstractModel::class)
            ->setMethods(['getActions', '__sleep', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $actions = $this->createPartialMock(Collection::class, ['asHtmlRecursive']);

        $this->_element->expects($this->any())
            ->method('getRule')
            ->willReturn($rule);

        $rule->expects($this->any())
            ->method('getActions')
            ->willReturn($actions);

        $actions->expects($this->once())
            ->method('asHtmlRecursive')
            ->willReturn('action html');

        $this->assertEquals('action html', $this->actions->render($this->_element));
    }
}
