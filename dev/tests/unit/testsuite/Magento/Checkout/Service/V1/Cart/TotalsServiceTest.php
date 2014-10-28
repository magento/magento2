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
namespace Magento\Checkout\Service\V1\Cart;

class TotalsServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $itemTotalsMapperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsMapperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsBuilderMock;

    /**
     * @var TotalsService
     */
    private $service;

    public function setUp()
    {
        $this->quoteMock = $this->getMock(
            'Magento\Sales\Model\Quote', [], [], '', false
        );
        $this->totalsBuilderMock = $this->getMock(
            'Magento\Checkout\Service\V1\Data\Cart\TotalsBuilder',
            ['populateWithArray', 'setItems', 'create'],
            [],
            '',
            false
        );
        $this->totalsMapperMock = $this->getMock(
            'Magento\Checkout\Service\V1\Data\Cart\TotalsMapper', [], [], '', false
        );
        $this->quoteRepositoryMock = $this->getMock(
            'Magento\Sales\Model\QuoteRepository', [], [], '', false
        );
        $this->itemTotalsMapperMock = $this->getMock(
            'Magento\Checkout\Service\V1\Data\Cart\Totals\ItemMapper', ['extractDto'], [], '', false
        );

        $this->service = new TotalsService(
            $this->totalsBuilderMock,
            $this->totalsMapperMock,
            $this->quoteRepositoryMock,
            $this->itemTotalsMapperMock
        );
    }

    public function testGetTotals()
    {
        $cartId = 12;
        $this->quoteRepositoryMock->expects($this->once())->method('get')->with($cartId)
            ->will($this->returnValue($this->quoteMock));

        $this->totalsMapperMock->expects($this->once())
            ->method('map')
            ->with($this->quoteMock)
            ->will($this->returnValue(array('test')));

        $item = $this->getMock('Magento\Sales\Model\Quote\Item', [], [], '', false);
        $this->quoteMock->expects($this->once())->method('getAllItems')->will($this->returnValue([$item]));
        $this->service->getTotals($cartId);
    }
} 
