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
namespace Magento\Sales\Model\Quote;

use Magento\Store\Model\ScopeInterface;

/**
 * Test class for \Magento\Sales\Model\Order
 */
class AddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Address
     */
    private $address;

    /**
     * @var \Magento\Sales\Model\Quote | \PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \Magento\Framework\App\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config', [], [], '', false);

        $this->address = $objectManager->getObject(
                'Magento\Sales\Model\Quote\Address',
                [
                    'scopeConfig' => $this->scopeConfig
                ]
            );
        $this->quote = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $this->address->setQuote($this->quote);
    }

    public function testValidateMiniumumAmountDisabled()
    {
        $storeId = 1;

        $this->quote->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with('sales/minimum_order/active', ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn(false);

        $this->assertTrue($this->address->validateMinimumAmount());
    }

    public function testValidateMiniumumAmountVirtual()
    {
        $storeId = 1;
        $scopeConfigValues = [
            ['sales/minimum_order/active', ScopeInterface::SCOPE_STORE, $storeId, true],
            ['sales/minimum_order/amount', ScopeInterface::SCOPE_STORE, $storeId, 20],
            ['sales/minimum_order/tax_including', ScopeInterface::SCOPE_STORE, $storeId, true]
        ];

        $this->quote->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->quote->expects($this->once())
            ->method('getIsVirtual')
            ->willReturn(true);
        $this->address->setAddressType(Address::TYPE_SHIPPING);

        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->willReturnMap($scopeConfigValues);


        $this->assertTrue($this->address->validateMinimumAmount());
    }

    public function testValidateMiniumumAmount()
    {
        $storeId = 1;
        $scopeConfigValues = [
            ['sales/minimum_order/active', ScopeInterface::SCOPE_STORE, $storeId, true],
            ['sales/minimum_order/amount', ScopeInterface::SCOPE_STORE, $storeId, 20],
            ['sales/minimum_order/tax_including', ScopeInterface::SCOPE_STORE, $storeId, true]
        ];

        $this->quote->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->quote->expects($this->once())
            ->method('getIsVirtual')
            ->willReturn(false);

        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->willReturnMap($scopeConfigValues);


        $this->assertTrue($this->address->validateMinimumAmount());
    }

    public function testValidateMiniumumAmountNegative()
    {
        $storeId = 1;
        $scopeConfigValues = [
            ['sales/minimum_order/active', ScopeInterface::SCOPE_STORE, $storeId, true],
            ['sales/minimum_order/amount', ScopeInterface::SCOPE_STORE, $storeId, 20],
            ['sales/minimum_order/tax_including', ScopeInterface::SCOPE_STORE, $storeId, true]
        ];

        $this->quote->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->quote->expects($this->once())
            ->method('getIsVirtual')
            ->willReturn(false);
        $this->address->setAddressType(Address::TYPE_SHIPPING);

        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->willReturnMap($scopeConfigValues);


        $this->assertTrue($this->address->validateMinimumAmount());
    }
}
