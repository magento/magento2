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

namespace Magento\Sales\Block\Adminhtml\Order\Create\Items;

class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid
     */
    protected $_block;

    /**
     * Initialize required data
     */
    protected function setUp()
    {
        $orderCreateMock = $this->getMock(
            'Magento\Sales\Model\AdminOrder\Create',
            array('__wakeup'),
            array(),
            '',
            false
        );

        $taxData = $this->getMockBuilder('Magento\Tax\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $coreData = $this->getMockBuilder('Magento\Core\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $sessionMock = $this->getMockBuilder('Magento\Backend\Model\Session\Quote')
            ->disableOriginalConstructor()
            ->setMethods(array('getQuote', '__wakeup'))
            ->getMock();

        $quoteMock = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(array('getStore', '__wakeup'))
            ->getMock();

        $storeMock = $this->getMockBuilder('Magento\Core\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(array('__wakeup', 'convertPrice'))
            ->getMock();
        $storeMock->expects($this->any())->method('convertPrice')->will($this->returnArgument(0));

        $quoteMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        $sessionMock->expects($this->any())->method('getQuote')->will($this->returnValue($quoteMock));

        $wishlistFactoryMock = $this->getMockBuilder('Magento\Wishlist\Model\WishlistFactory')
            ->setMethods(array('methods', '__wakeup'))
            ->getMock();

        $giftMessageSave = $this->getMockBuilder('Magento\Giftmessage\Model\Save')
            ->setMethods(array('__wakeup'))
            ->disableOriginalConstructor()
            ->getMock();

        $taxConfig = $this->getMockBuilder('Magento\Tax\Model\Config')->disableOriginalConstructor()->getMock();

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_block = $helper->getObject('Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid',
            array(
                'wishlistFactory' => $wishlistFactoryMock,
                'giftMessageSave' => $giftMessageSave,
                'taxConfig' => $taxConfig,
                'taxData' => $taxData,
                'sessionQuote' => $sessionMock,
                'orderCreate' => $orderCreateMock,
                'coreData' => $coreData
            )
        );
    }

    /**
     * @param array $itemData
     * @param string $expectedMessage
     * @param string $productType
     * @dataProvider tierPriceDataProvider
     */
    public function testTierPriceInfo($itemData, $expectedMessage, $productType)
    {
        $itemMock = $this->_prepareItem($itemData, $productType);
        $result = $this->_block->getTierHtml($itemMock);

        $this->assertEquals($expectedMessage, $result);
    }

    /**
     * Provider for test
     *
     * @return array
     */
    public function tierPriceDataProvider()
    {
        return array(
            array(
                array(array('price' => 100, 'price_qty' => 1)),
                '1 with 100% discount each',
                \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
            ),
            array(
                array(array('price' => 100, 'price_qty' => 1), array('price' => 200, 'price_qty' => 2)),
                '1 with 100% discount each<br/>2 with 200% discount each',
                \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
            ),
            array(
                array(array('price' => 50, 'price_qty' => 2)),
                '2 for 50',
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            ),
            array(
                array(array('price' => 50, 'price_qty' => 2), array('price' => 150, 'price_qty' => 3)),
                '2 for 50<br/>3 for 150',
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            ),
            array(
                0,
                '',
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            ),
        );
    }

    /**
     * @param array|int $tierPrices
     * @param string $productType
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Quote\Item
     */
    protected function _prepareItem($tierPrices, $productType)
    {
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(array('getTierPrice', '__wakeup'))
            ->getMock();
        $product->expects($this->once())
            ->method('getTierPrice')
            ->will($this->returnValue($tierPrices));
        $item = $this->getMock('Magento\Sales\Model\Quote\Item',
            array(), array('getProduct', 'getProductType'), '', false);
        $item->expects($this->once())
            ->method('getProduct')
            ->will($this->returnValue($product));

        $calledTimes = $tierPrices ? 'once' : 'never';
        $item->expects($this->$calledTimes())
            ->method('getProductType')
            ->will($this->returnValue($productType));
        return $item;
    }
}
