<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config\Fieldset;

use Magento\Backend\Model\Auth\Session;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class GroupTest extends TestCase
{
    /**
     * @var Group
     */
    private $_model;

    /**
     * @var AbstractElement
     */
    private $_element;

    /**
     * @var Session|MockObject
     */
    private $_authSession;

    /**
     * @var User|MockObject
     */
    private $_user;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Group|MockObject
     */
    private $_group;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->_group = $this->createMock(Group::class);
        $this->_element = $this->getMockForAbstractClass(
            AbstractElement::class,
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
        $this->_user = $this->createMock(User::class);
        $this->_authSession = $this->createMock(Session::class);
        $this->_authSession->expects($this->any())
            ->method('__call')
            ->with('getUser')
            ->willReturn($this->_user);
        $secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $secureRendererMock->method('renderEventListenerAsTag')
            ->willReturnCallback(
                function (string $event, string $js, string $selector): string {
                    return "<script>document.querySelector('$selector').$event = function () { $js };</script>";
                }
            );
        $secureRendererMock->method('renderStyleAsTag')
            ->willReturnCallback(
                function (string $style, string $selector): string {
                    return "<style>$selector { $style }</style>";
                }
            );

        $this->_model = $helper->getObject(
            \Magento\Paypal\Block\Adminhtml\System\Config\Fieldset\Group::class,
            ['authSession' => $this->_authSession, 'secureRenderer' => $secureRendererMock]
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
        $this->assertStringContainsString(
            '<input id="' . $this->_element->getHtmlId() . '-state" name="config_state['
            . $this->_element->getId() . ']" type="hidden" value="' . $expected . '" />',
            $html
        );
    }

    /**
     * @return array
     */
    public static function isCollapseStateDataProvider()
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
