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

namespace Magento\Checkout\Service\V1;

class QuoteLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QuoteLoader
     */
    protected $quoteLoader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    protected function setUp()
    {
        $this->quoteFactoryMock = $this->getMock('\Magento\Sales\Model\QuoteFactory', ['create'], [], '', false);
        $this->quoteMock =
            $this->getMock(
                '\Magento\Sales\Model\Quote',
                ['setStoreId', 'load', 'getId', '__wakeup', 'getIsActive'],
                [],
                '',
                false
            );
        $this->quoteLoader = new QuoteLoader($this->quoteFactoryMock);
    }

    public function testLoadWithId()
    {
        $storeId = 1;
        $cartId = 45;
        $this->quoteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('setStoreId')->with($storeId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('load')->with($cartId);
        $this->quoteMock->expects($this->once())->method('getId')->will($this->returnValue(33));
        $this->quoteMock->expects($this->once())->method('getIsActive')->will($this->returnValue(true));

        $this->assertEquals($this->quoteMock, $this->quoteLoader->load($cartId, $storeId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 34
     */
    public function testLoadWithoutId()
    {
        $storeId = 12;
        $cartId = 34;
        $this->quoteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('setStoreId')->with($storeId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())->method('load')->with($cartId);
        $this->quoteMock->expects($this->once())->method('getId')->will($this->returnValue(false));
        $this->quoteLoader->load($cartId, $storeId);
    }
}
