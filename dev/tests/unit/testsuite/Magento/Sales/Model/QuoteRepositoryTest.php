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

namespace Magento\Sales\Model;

class QuoteRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QuoteRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    protected function setUp()
    {
        $objectManager =new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->quoteFactoryMock = $this->getMock('\Magento\Sales\Model\QuoteFactory', ['create'], [], '', false);
        $this->storeManagerMock = $this->getMock('\Magento\Framework\StoreManagerInterface');
        $this->quoteMock =
            $this->getMock('\Magento\Sales\Model\Quote', ['load', 'getIsActive', 'getId', '__wakeup'], [], '', false);
        $this->storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->model = $objectManager->getObject(
            'Magento\Sales\Model\QuoteRepository',
            [
                'quoteFactory' => $this->quoteFactoryMock,
                'storeManager' => $this->storeManagerMock,
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 14
     */
    public function testGetWithExceptionById()
    {
        $cartId = 14;

        $this->quoteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->quoteMock));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($this->storeMock));
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->will($this->returnValue($this->storeMock));
        $this->quoteMock->expects($this->once())->method('getId')->will($this->returnValue(false));

        $this->model->get($cartId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 15
     */
    public function testGetWithExceptionByIsActive()
    {
        $cartId = 15;

        $this->quoteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->quoteMock));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($this->storeMock));
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->will($this->returnValue($this->storeMock));
        $this->quoteMock->expects($this->once())->method('getId')->will($this->returnValue(true));
        $this->quoteMock->expects($this->once())->method('getIsActive')->will($this->returnValue(0));

        $this->model->get($cartId);
    }

    public function testGet()
    {
        $cartId = 15;

        $this->quoteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($this->quoteMock));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('getId')->will($this->returnValue($this->storeMock));
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->will($this->returnValue($this->storeMock));
        $this->quoteMock->expects($this->once())->method('getId')->will($this->returnValue(true));
        $this->quoteMock->expects($this->once())->method('getIsActive')->will($this->returnValue(1));

        $this->assertEquals($this->quoteMock, $this->model->get($cartId));
    }
}

