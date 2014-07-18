<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Data\Form;

use Magento\Framework\Data\Form;

class FormKeyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mathRandomMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    protected function setUp()
    {
        $this->mathRandomMock = $this->getMock('Magento\Framework\Math\Random', [], [], '', false);
        $methods = ['setData', 'getData'];
        $this->sessionMock = $this->getMock('Magento\Framework\Session\SessionManager', $methods, [], '', false);
        $this->formKey = new FormKey(
            $this->mathRandomMock,
            $this->sessionMock
        );
    }

    public function testGetFormKeyNotExist()
    {
        $valueMap = [
            [FormKey::FORM_KEY, false, null],
            [FormKey::FORM_KEY, false, 'random_string']
        ];
        $this->sessionMock
            ->expects($this->any())
            ->method('getData')
            ->will($this->returnValueMap($valueMap));
        $this->mathRandomMock
            ->expects($this->once())
            ->method('getRandomString')
            ->with(16)
            ->will($this->returnValue('random_string'));
        $this->sessionMock->expects($this->once())->method('setData')->with(FormKey::FORM_KEY, 'random_string');
        $this->formKey->getFormKey();
    }

    public function testGetFormKeyExists()
    {
        $this->sessionMock
            ->expects($this->exactly(2))
            ->method('getData')
            ->with(FormKey::FORM_KEY)
            ->will($this->returnValue('random_string'));
        $this->mathRandomMock
            ->expects($this->never())
            ->method('getRandomString');
        $this->sessionMock->expects($this->never())->method('setData');
        $this->assertEquals('random_string', $this->formKey->getFormKey());
    }
}
