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
namespace Magento\Weee\Model\Total\Quote;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class WeeeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_weeeDataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_quoteItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mageObjMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerInterfaceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_weeeTaxMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_taxHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigInterfaceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_quoteModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_addressMock;

    /**
     * @var \Magento\Weee\Model\Total\Quote\Weee
     */
    protected $_model;

    protected function setUp()
    {
        $this->_initializeMockObjects();
        $this->_prepareStaticMockExpects();
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            '\Magento\Weee\Model\Total\Quote\Weee',
            array(
                'weeeData' => $this->_weeeDataMock,
                'taxConfig' =>  $this->_configMock
            )
        );
    }

    /**
     * Initialize mock objects
     */
    protected function _initializeMockObjects()
    {
        $quoteItemMethods = [
            '__wakeup',
            'getProduct',
            'setWeeeTaxAppliedAmount',
            'setBaseWeeeTaxAppliedAmount',
            'setWeeeTaxAppliedRowAmount',
            'setBaseWeeeTaxAppliedRowAmnt',
            'getHasChildren',
            'getChildren',
            'isChildrenCalculated',
            'getTotalQty',
            'getQuote'
        ];

        $this->_storeManagerInterfaceMock = $this->getMock(
            'Magento\Store\Model\StoreManagerInterface', [], [], '', false
        );
        $this->_weeeTaxMock = $this->getMock(
            '\Magento\Weee\Model\Tax', ['__wakeup', 'getProductWeeeAttributes'], [], '', false
        );
        $this->_taxHelperMock = $this->getMock('\Magento\Tax\Helper\Data', [], [], '', false);
        $this->_registryMock = $this->getMock('\Magento\Framework\Registry', [], [], '', false);
        $this->_scopeConfigInterfaceMock = $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface', ['isSetFlag', 'getValue'], [], '', false
        );
        $this->_weeeDataMock = $this->getMock('\Magento\Weee\Helper\Data', array(), array(), '', false);
        $this->_configMock = $this->getMock('\Magento\Tax\Model\Config', ['priceIncludesTax'], [], '', false);
        $this->_objectMock = $this->getMock('\Magento\Framework\Object', [], [], '', false);
        $this->_storeMock = $this->getMock('\Magento\Store\Model\Store', ['__wakeup', 'convertPrice'], [], '', false);
        $this->_quoteItemMock = $this->getMock('Magento\Sales\Model\Quote\Item', $quoteItemMethods, [], '', false);
        $this->_productModelMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->_quoteModelMock = $this->getMock('\Magento\Sales\Model\Quote',
            ['__wakeup', 'getBillingAddress', 'getStore'], [], '', false);
        $this->_addressMock = $this->getMock('\Magento\Sales\Model\Quote\Address', [
            '__wakeup',
            'unsSubtotalInclTax',
            'unsBaseSubtotalInclTax',
            'getAllItems',
            'getQuote',
            'getAllNonNominalItems',
            'getPrice'
        ], [], '', false);
    }

    /**
     * Prepare expects for mocked objects
     */
    protected function _prepareStaticMockExpects()
    {
        $this->_addressMock->expects($this->any())->method('getQuote')
            ->will($this->returnValue($this->_quoteModelMock));
        $this->_addressMock->expects($this->any())->method('getAllItems')
            ->will($this->returnValue($this->_quoteModelMock));
        $this->_quoteModelMock->expects($this->any())->method('getStore')
            ->will($this->returnValue($this->_storeMock));
        $this->_quoteModelMock->expects($this->any())->method('getBillingAddress')
            ->will($this->returnValue($this->_addressMock));
        $this->_quoteModelMock->expects($this->any())->method('getPrice')
            ->will($this->returnValue(1));
        $this->_quoteItemMock->expects($this->any())->method('getProduct')
            ->will($this->returnValue($this->_productModelMock));
        $this->_quoteItemMock->expects($this->any())->method('getTotalQty')
            ->will($this->returnValue(1));
        $this->_quoteItemMock->expects($this->any())->method('getQuote')
            ->will($this->returnValue($this->_quoteModelMock));
        $this->_scopeConfigInterfaceMock->expects($this->any())->method('isSetFlag')
            ->will($this->returnValue(true));
        $this->_weeeTaxMock->expects($this->any())->method('getProductWeeeAttributes')
            ->will($this->returnValue(array($this->_objectMock)));
        $this->_weeeDataMock->expects($this->any())->method('getProductWeeeAttributes')
            ->will($this->returnValue(array($this->_objectMock)));
        $this->_weeeDataMock->expects($this->any())->method('getApplied')
            ->will($this->returnValue(array()));
        $this->_storeMock->expects($this->any())->method('convertPrice')
            ->will($this->returnValue(1));
    }

    /**
     * Collect items and apply discount to weee
     */
    public function testCollectWithAddItemDiscountPrices()
    {
        $this->_addressMock->expects($this->any())->method('getAllNonNominalItems')
            ->will($this->returnValue(array($this->_quoteItemMock)));
        $this->_weeeDataMock->expects($this->any())->method('isDiscounted')
            ->will($this->returnValue(true));
        $this->_weeeDataMock->expects($this->any())->method('isTaxable')
            ->will($this->returnValue(false));
        $this->_weeeDataMock->expects($this->once())->method('addItemDiscountPrices');
        $this->_weeeDataMock->expects($this->any())->method('isEnabled')
            ->will($this->returnValue(true));
        $this->_model->collect($this->_addressMock);
    }

    /**
     * Collect items without applying discount to weee
     */
    public function testCollectWithoutAddItemDiscountPrices()
    {
        $this->_addressMock->expects($this->any())->method('getAllNonNominalItems')
            ->will($this->returnValue(array($this->_quoteItemMock)));
        $this->_weeeDataMock->expects($this->any())->method('isDiscounted')
            ->will($this->returnValue(false));
        $this->_weeeDataMock->expects($this->any())->method('isTaxable')
            ->will($this->returnValue(false));
        $this->_weeeDataMock->expects($this->never())->method('addItemDiscountPrices');
        $this->_weeeDataMock->expects($this->any())->method('isEnabled')
            ->will($this->returnValue(true));
        $this->_model->collect($this->_addressMock);
    }

    /**
     * Collect items without address item
     */
    public function testCollectWithoutAddressItem()
    {
        $this->_addressMock->expects($this->any())->method('getAllNonNominalItems')
            ->will($this->returnValue(array()));
        $this->_addressMock->expects($this->never())->method('setAppliedTaxesReset');
        $this->_model->collect($this->_addressMock);
    }

    /**
     * Collect items with child
     */
    public function testCollectWithChildItem()
    {
        $this->_addressMock->expects($this->any())->method('getAllNonNominalItems')
            ->will($this->returnValue(array($this->_quoteItemMock)));
        $this->_weeeDataMock->expects($this->any())->method('isDiscounted')
            ->will($this->returnValue(false));
        $this->_weeeDataMock->expects($this->any())->method('isTaxable')
            ->will($this->returnValue(false));
        $this->_weeeDataMock->expects($this->any())->method('isEnabled')
            ->will($this->returnValue(true));
        $this->_quoteItemMock->expects($this->once())->method('isChildrenCalculated')
            ->will($this->returnValue(true));
        $this->_model->collect($this->_addressMock);
    }

    /**
     * Collect items with price that includes tax
     *
     * @param array
     */
    public function testCollectPriceIncludesTax()
    {
        $this->_addressMock->expects($this->any())->method('getAllNonNominalItems')
            ->will($this->returnValue(array($this->_quoteItemMock)));
        $this->_addressMock->expects($this->once())->method('getAllNonNominalItems');
        $this->_addressMock->expects($this->once())->method('getAllNonNominalItems');
        $this->_weeeDataMock->expects($this->any())->method('isDiscounted')
            ->will($this->returnValue(true));
        $this->_weeeDataMock->expects($this->once())->method('addItemDiscountPrices');
        $this->_weeeDataMock->expects($this->any())->method('isTaxable')
            ->will($this->returnValue(true));
        $this->_weeeDataMock->expects($this->any())->method('isEnabled')
            ->will($this->returnValue(true));
        $this->_configMock->expects($this->once())->method('priceIncludesTax')
            ->will($this->returnValue(false));
        $this->_model->collect($this->_addressMock);
    }

    /**
     * Collect items with price that does not include tax
     *
     * @param array
     */
    public function testCollectPriceNotIncludesTax()
    {
        $this->_addressMock->expects($this->any())->method('getAllNonNominalItems')
            ->will($this->returnValue(array($this->_quoteItemMock)));
        $this->_weeeDataMock->expects($this->any())->method('isDiscounted')
            ->will($this->returnValue(true));
        $this->_weeeDataMock->expects($this->once())->method('addItemDiscountPrices');
        $this->_weeeDataMock->expects($this->any())->method('isTaxable')
            ->will($this->returnValue(true));
        $this->_weeeDataMock->expects($this->any())->method('isEnabled')
            ->will($this->returnValue(true));
        $this->_configMock->expects($this->once())->method('priceIncludesTax')
            ->will($this->returnValue(true));
        $this->_model->collect($this->_addressMock);
    }

    /**
     * Collect taxable items
     */
    public function testCollectTaxable()
    {
        $this->_addressMock->expects($this->any())->method('getAllNonNominalItems')
            ->will($this->returnValue(array($this->_quoteItemMock)));
        $this->_addressMock->expects($this->once())->method('unsSubtotalInclTax');
        $this->_addressMock->expects($this->once())->method('unsBaseSubtotalInclTax');
        $this->_weeeDataMock->expects($this->any())->method('isDiscounted')
            ->will($this->returnValue(true));
        $this->_weeeDataMock->expects($this->once())->method('addItemDiscountPrices');
        $this->_weeeDataMock->expects($this->any())->method('isTaxable')
            ->will($this->returnValue(true));
        $this->_weeeDataMock->expects($this->any())->method('isEnabled')
            ->will($this->returnValue(true));
        $this->_configMock->expects($this->once())->method('priceIncludesTax')
            ->will($this->returnValue(true));
        $this->_model->collect($this->_addressMock);
    }

    /**
     * Collect does not taxable items
     */
    public function testCollectDataStoreDisabled()
    {
        $this->_addressMock->expects($this->any())->method('getAllNonNominalItems')
            ->will($this->returnValue(array($this->_quoteItemMock)));
        $this->_addressMock->expects($this->never())->method('unsSubtotalInclTax');
        $this->_addressMock->expects($this->never())->method('unsBaseSubtotalInclTax');
        $this->_weeeDataMock->expects($this->any())->method('isDiscounted')
            ->will($this->returnValue(true));
        $this->_weeeDataMock->expects($this->any())->method('isTaxable')
            ->will($this->returnValue(false));
        $this->_weeeDataMock->expects($this->any())->method('includeInSubtotal')
            ->will($this->returnValue(false));
        $this->_weeeDataMock->expects($this->once(0))->method('isEnabled')
            ->will($this->returnValue(false));
        $this->_configMock->expects($this->never())->method('priceIncludesTax')
            ->will($this->returnValue(true));
        $this->_model->collect($this->_addressMock);
    }

    /**
     * Collect items and apply discount to weee
     */
    public function testCollectWithChildren()
    {
        $childQuoteItemMock = $this->getMock('Magento\Sales\Model\Quote\Item', [], [], '', false);

        $this->_addressMock->expects($this->any())->method('getAllNonNominalItems')
            ->will($this->returnValue(array($this->_quoteItemMock)));
        $this->_quoteItemMock->expects($this->any())->method('getHasChildren')
            ->will($this->returnValue(true));
        $this->_quoteItemMock->expects($this->any())->method('isChildrenCalculated')
            ->will($this->returnValue(true));
        $this->_quoteItemMock->expects($this->any())->method('getChildren')
            ->will($this->returnValue(array($childQuoteItemMock)));
        $this->_weeeDataMock->expects($this->any())->method('isDiscounted')
            ->will($this->returnValue(true));
        $this->_weeeDataMock->expects($this->any())->method('isTaxable')
            ->will($this->returnValue(false));
        $this->_weeeDataMock->expects($this->once())->method('addItemDiscountPrices');
        $this->_weeeDataMock->expects($this->any())->method('isEnabled')
            ->will($this->returnValue(true));
        $this->_model->collect($this->_addressMock);
    }

    public function testCollectWeeeIncludeInSubtotal()
    {
        $this->_addressMock->expects($this->any())->method('getAllNonNominalItems')
            ->will($this->returnValue(array($this->_quoteItemMock)));
        $this->_weeeDataMock->expects($this->any())->method('isDiscounted')
            ->will($this->returnValue(true));
        $this->_weeeDataMock->expects($this->any())->method('isTaxable')
            ->will($this->returnValue(false));
        $this->_weeeDataMock->expects($this->once())->method('addItemDiscountPrices');
        $this->_weeeDataMock->expects($this->any())->method('isEnabled')
            ->will($this->returnValue(true));
        $this->_weeeDataMock->expects($this->any())->method('includeInSubtotal')
            ->will($this->returnValue(true));
        $this->_model->collect($this->_addressMock);
    }

    /**
     * Collect empty items
     */
    public function testCollectWithoutItems()
    {
        $this->_addressMock->expects($this->any())->method('getAllNonNominalItems')
            ->will($this->returnValue(null));
        $this->assertEquals($this->_model, $this->_model->collect($this->_addressMock));
    }

    /**
     * Fetch method test
     */
    public function testFetch()
    {
        $this->assertEquals($this->_model, $this->_model->fetch($this->_addressMock));
    }

    /**
     * Process configuration array
     */
    public function testProcessConfigArray()
    {
        $this->assertEquals(
            $this->_configMock, $this->_model->processConfigArray($this->_configMock, $this->_storeMock)
        );
    }

    /**
     * Get label
     */
    public function testGetLabel()
    {
        $this->assertEquals('', $this->_model->getLabel());
    }
}
