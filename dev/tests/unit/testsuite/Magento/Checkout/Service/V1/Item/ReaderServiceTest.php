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

namespace Magento\Checkout\Service\V1\Item;

use \Magento\Checkout\Service\V1\Data\Cart\Item as Item;

class ReaderServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMapperMock;

    protected function setUp()
    {
        $this->quoteRepositoryMock = $this->getMock('Magento\Sales\Model\QuoteRepository', [], [], '', false);
        $this->itemMapperMock =
            $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\ItemMapper', ['extractDto'], [], '', false);
        $this->service = new ReadService($this->quoteRepositoryMock, $this->itemMapperMock);
    }

    public  function testGetList()
    {
        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('get')
            ->with(33)
            ->will($this->returnValue($quoteMock));
        $itemMock = $this->getMock('\Magento\Sales\Model\Quote\Item',
            ['getSku', 'getName', 'getPrice', 'getQty', 'getProductType', '__wakeup'], [], '', false);
        $quoteMock->expects($this->any())->method('getAllItems')->will($this->returnValue(array($itemMock)));
        $testData = [
            Item::ITEM_ID => 7,
            Item::SKU => 'prd_SKU',
            Item::NAME => 'prd_NAME',
            Item::PRICE => 100.15,
            Item::QTY => 16,
            Item::PRODUCT_TYPE => 'simple',
        ];

        $this->itemMapperMock
            ->expects($this->once())
            ->method('extractDto')
            ->with($itemMock)
            ->will($this->returnValue($testData));
        $this->assertEquals([$testData], $this->service->getList(33));
    }
}
