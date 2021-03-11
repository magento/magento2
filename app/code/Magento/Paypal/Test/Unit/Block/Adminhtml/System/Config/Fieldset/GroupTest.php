<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config\Fieldset;

class GroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Group
     */
    private $_model;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    private $_element;

    /**
     * @var \Magento\Backend\Model\Auth\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $_authSession;

    /**
     * @var \Magento\User\Model\User|\PHPUnit\Framework\MockObject\MockObject
     */
    private $_user;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Group|\PHPUnit\Framework\MockObject\MockObject
     */
    private $_group;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_group = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Group::class);
        $this->_element = $this->getMockForAbstractClass(
            \Magento\Framework\Data\Form\Element\AbstractElement::class,
            [],
            '',
            false,
            true,
            true,
            ['getHtmlId', 'getElementHtml', 'getName', 'getElements', 'getId']
        );
        $this->_element->expects($this->any())
            ->method('getHtmlId')
            ->willReturn('html id');
        $this->_element->expects($this->any())
            ->method('getElementHtml')
            ->willReturn('element html');
        $this->_element->expects($this->any())
            ->method('getName')
            ->willReturn('name');
        $this->_element->expects($this->any())
            ->method('getElements')
            ->willReturn([]);
        $this->_element->expects($this->any())
            ->method('getId')
            ->willReturn('id');
        $this->_user = $this->createMock(\Magento\User\Model\User::class);
        $this->_authSession = $this->createMock(\Magento\Backend\Model\Auth\Session::class);
        $this->_authSession->expects($this->any())
            ->method('__call')
            ->with('getUser')
            ->willReturn($this->_user);
        $this->_model = $helper->getObject(
            \Magento\Paypal\Block\Adminhtml\System\Config\Fieldset\Group::class,
            ['authSession' => $this->_authSession]
        );
        $this->_model->setGroup($this->_group);
    }

    /**
     * @param mixed $expanded
     * @param int $expected
     * @dataProvider isCollapseStateDataProvider
     */
    public function testIsCollapseState($expanded, $expected)
    {
        $this->_user->setExtra(['configState' => []]);
        $this->_element->setGroup(isset($expanded) ? ['expanded' => $expanded] : []);
        $html = $this->_model->render($this->_element);
        $this->assertStringContainsString('<input id="' . $this->_element->getHtmlId() . '-state" name="config_state['
            . $this->_element->getId() . ']" type="hidden" value="' . $expected . '" />', $html);
    }

    /**
     * @return array
     */
    public function isCollapseStateDataProvider()
    {
        return [
            [null, 0],
            [false, 0],
            ['', 0],
            [1, 1],
            ['1', 1],
        ];
    }
}
