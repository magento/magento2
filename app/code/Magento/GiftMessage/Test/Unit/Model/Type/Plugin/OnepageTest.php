<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\GiftMessage\Test\Unit\Model\Type\Plugin;

use Magento\GiftMessage\Model\Type\Plugin\Onepage;

class OnepageTest extends \PHPUnit\Framework\TestCase
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
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->messageMock = $this->createMock(\Magento\GiftMessage\Model\GiftMessageManager::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);

        $this->plugin = $objectManager->getObject(
            \Magento\GiftMessage\Model\Type\Plugin\Onepage::class,
            [
                'message' => $this->messageMock,
                'request' => $this->requestMock,
            ]
        );
    }

    public function testAfterSaveShippingMethodWithEmptyResult()
    {
        $subjectMock = $this->createMock(\Magento\Checkout\Model\Type\Onepage::class);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('giftmessage')
            ->will($this->returnValue('giftMessage'));
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $subjectMock->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->messageMock->expects($this->once())->method('add')->with('giftMessage', $quoteMock);

        $this->assertEquals([], $this->plugin->afterSaveShippingMethod($subjectMock, []));
    }

    public function testAfterSaveShippingMethodWithNotEmptyResult()
    {
        $subjectMock = $this->createMock(\Magento\Checkout\Model\Type\Onepage::class);
        $this->assertEquals(
            ['expected result'],
            $this->plugin->afterSaveShippingMethod($subjectMock, ['expected result']));
    }
}
