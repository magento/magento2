<?php
/** 
 * 
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

namespace Magento\GiftMessage\Model\Type\Plugin;

class OnepageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Onepage
     */
    protected $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    protected function setUp()
    {
        $objectManager =new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->messageMock = $this->getMock('\Magento\GiftMessage\Model\GiftMessageManager', [], [], '', false);
        $this->requestMock = $this->getMock('\Magento\Framework\App\RequestInterface');

        $this->plugin = $objectManager->getObject(
            'Magento\GiftMessage\Model\Type\Plugin\Onepage',
            [
                'message' => $this->messageMock,
                'request' => $this->requestMock,
            ]
        );
    }

    public function testAfterSaveShippingMethodWithEmptyResult()
    {
        $subjectMock = $this->getMock('\Magento\Checkout\Model\Type\Onepage', [], [], '', false);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('giftmessage')
            ->will($this->returnValue('giftMessage'));
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $subjectMock->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->messageMock->expects($this->once())->method('add')->with('giftMessage', $quoteMock);

        $this->assertEquals([], $this->plugin->afterSaveShippingMethod($subjectMock, []));
    }

    public function testAfterSaveShippingMethodWithNotEmptyResult()
    {
        $subjectMock = $this->getMock('\Magento\Checkout\Model\Type\Onepage', [], [], '', false);
        $this->assertEquals(
            ['expected result'],
            $this->plugin->afterSaveShippingMethod($subjectMock, ['expected result']));
    }
}

