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

namespace Magento\Wishlist\Model;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Wishlist\Model\Item
     */
    protected $wishlistItem;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $contextMock = $this->getMock('\Magento\Framework\Model\Context', [], [], '', false);
        $registryMock = $this->getMock('\Magento\Framework\Registry', [], [], '', false);
        $storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface', [], [], '');
        $dateMock = $this->getMock('\Magento\Framework\Stdlib\DateTime\DateTime', [], [], '', false);
        $productFactoryMock = $this->getMock('\Magento\Catalog\Model\ProductFactory', [], [], '', false);
        $catalogUrlMock = $this->getMock('\Magento\Catalog\Model\Resource\Url', [], [], '', false);
        $wishlistOptFactoryMock = $this->getMock('\Magento\Wishlist\Model\Item\OptionFactory', [], [], '', false);
        $wishlOptionCollectionFactoryMock = $this->getMock(
            'Magento\Wishlist\Model\Resource\Item\Option\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $productTypeConfigMock = $this->getMock(
            '\Magento\Catalog\Model\ProductTypes\ConfigInterface',
            [],
            [],
            ''
        );

        $this->wishlistItem = $this->objectManager->getObject(
            '\Magento\Wishlist\Model\Item',
            [
                'context' => $contextMock,
                'registry' => $registryMock,
                'storeManager' => $storeManagerMock,
                'date' => $dateMock,
                'productFactory' => $productFactoryMock,
                'catalogUrl' => $catalogUrlMock,
                'wishlistOptFactory' => $wishlistOptFactoryMock,
                'wishlOptionCollectionFactory' => $wishlOptionCollectionFactoryMock,
                'productTypeConfig' => $productTypeConfigMock,
            ]
        );
    }

    public function testCompareOptionsPositive()
    {
        $code = 'someOption';
        $optionValue = 100;
        $optionsOneMock = $this->getMock(
            '\Magento\Sales\Model\Quote\Item',
            ['getCode', '__wakeup', 'getValue'],
            [],
            '',
            false
        );
        $optionsTwoMock = $this->getMock(
            '\Magento\Sales\Model\Quote\Item',
            ['getValue', '__wakeup'],
            [],
            '',
            false
        );

        $optionsOneMock->expects($this->once())->method('getCode')->will($this->returnValue($code));
        $optionsOneMock->expects($this->once())->method('getValue')->will($this->returnValue($optionValue));
        $optionsTwoMock->expects($this->once())->method('getValue')->will($this->returnValue($optionValue));

        $result = $this->wishlistItem->compareOptions(
            [$code => $optionsOneMock],
            [$code => $optionsTwoMock]
        );

        $this->assertTrue($result);
    }

    public function testCompareOptionsNegative()
    {
        $code = 'someOption';
        $optionOneValue = 100;
        $optionTwoValue = 200;
        $optionsOneMock = $this->getMock(
            '\Magento\Sales\Model\Quote\Item',
            ['getCode', '__wakeup', 'getValue'],
            [],
            '',
            false
        );
        $optionsTwoMock = $this->getMock(
            '\Magento\Sales\Model\Quote\Item',
            ['getValue', '__wakeup'],
            [],
            '',
            false
        );

        $optionsOneMock->expects($this->once())->method('getCode')->will($this->returnValue($code));
        $optionsOneMock->expects($this->once())->method('getValue')->will($this->returnValue($optionOneValue));
        $optionsTwoMock->expects($this->once())->method('getValue')->will($this->returnValue($optionTwoValue));

        $result = $this->wishlistItem->compareOptions(
            [$code => $optionsOneMock],
            [$code => $optionsTwoMock]
        );

        $this->assertFalse($result);
    }

    public function testCompareOptionsNegativeOptionsTwoHaveNotOption()
    {
        $code = 'someOption';
        $optionsOneMock = $this->getMock('\Magento\Sales\Model\Quote\Item', ['getCode', '__wakeup'], [], '', false);
        $optionsTwoMock = $this->getMock('\Magento\Sales\Model\Quote\Item', [], [], '', false);

        $optionsOneMock->expects($this->once())->method('getCode')->will($this->returnValue($code));

        $result = $this->wishlistItem->compareOptions(
            [$code => $optionsOneMock],
            ['someOneElse' => $optionsTwoMock]
        );

        $this->assertFalse($result);
    }
}
