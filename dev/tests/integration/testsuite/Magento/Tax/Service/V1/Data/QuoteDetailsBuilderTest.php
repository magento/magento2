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
namespace Magento\Tax\Service\V1\Data;


class QuoteDetailsBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\ObjectManager */
    private $objectManager;

    /** @var QuoteDetailsBuilder */
    private $builder;

    /* @var QuoteDetails\ItemBuilder */
    private $itemBuilder;

    /* @var \Magento\Customer\Service\V1\Data\AddressBuilder */
    private $addressBuilder;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->builder = $this->objectManager->create('Magento\Tax\Service\V1\Data\QuoteDetailsBuilder');
        $this->itemBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\QuoteDetails\ItemBuilder');
        $this->addressBuilder = $this->objectManager->create('\Magento\Customer\Service\V1\Data\AddressBuilder');
    }

    /**
     * @param array $dataArray
     * @param array $items
     * @dataProvider createDataProvider
     */
    public function testCreateWithPopulateWithArray($dataArray, $items = [])
    {
        if (!empty($items)) {
            $dataArray[QuoteDetails::KEY_ITEMS] = $items;
        }
        $taxRate = $this->builder->populateWithArray($dataArray)->create();

        $this->assertInstanceOf('\Magento\Tax\Service\V1\Data\QuoteDetails', $taxRate);
        $this->assertEquals($dataArray, $taxRate->__toArray());
    }

    /**
     * @param array $dataArray
     * @param array $items
     * @dataProvider createDataProvider
     */
    public function testPopulate($dataArray, $items = [])
    {
        if (!empty($items)) {
            $dataArray[QuoteDetails::KEY_ITEMS] = $items;
        }
        $taxRate = $this->builder->populateWithArray($dataArray)->create();
        $taxRate2 = $this->builder->populate($taxRate)->create();

        $this->assertEquals($taxRate, $taxRate2);
    }

    public function createDataProvider()
    {
        $data = $this->getData()['dataMerged'];
        $items = $data[QuoteDetails::KEY_ITEMS];
        unset($data[QuoteDetails::KEY_ITEMS]);

        return [
            'withEmptyData' => [[],[]],
            'withEmptyQuoteItems' => [$data],
            'withQuoteItems' => [[], $items],
            'withQuoteDetailsAndItems' => [$data, $items]
        ];
    }

    public function testMergeDataObjects()
    {
        $data = $this->getData();
        $taxRate = $this->builder->populateWithArray($data['dataMerged'])->create();
        $taxRate1 = $this->builder->populateWithArray($data['data1'])->create();
        $taxRate2 = $this->builder->populateWithArray($data['data2'])->create();
        $taxRateMerged = $this->builder->mergeDataObjects($taxRate1, $taxRate2);
        $this->assertEquals($taxRate->__toArray(), $taxRateMerged->__toArray());
    }

    public function testMergeDataObjectWithArray()
    {
        $data = $this->getData();

        $taxRate = $this->builder->populateWithArray($data['dataMerged'])->create();
        $taxRate1 = $this->builder->populateWithArray($data['data1'])->create();
        $taxRateMerged = $this->builder->mergeDataObjectWithArray($taxRate1, $data['data2']);
        $this->assertEquals($taxRate->__toArray(), $taxRateMerged->__toArray());
    }

    public function testSetCustomerId()
    {
        $customerId = 1;
        $this->builder->setCustomerId($customerId);
        $quoteDetails = $this->builder->create();
        $this->assertEquals($customerId, $quoteDetails->getCustomerId());
    }

    /**
     * @return array
     */
    protected function getData()
    {
        $addressData = [
            'id' => 14,
            'default_shipping' => true,
            'default_billing' => true,
            'company' => 'Company Name',
            'fax' => '(555) 555-5555',
            'middlename' => 'Mid',
            'prefix' => 'Mr.',
            'suffix' => 'Esq.',
            'vat_id' => 'S45',
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'street' => ['7700 W Parmer Ln'],
            'city' => 'Austin',
            'country_id' => 'US',
            'postcode' => '78620',
            'telephone' => '5125125125',
            'region' => ['region_id' => 0, 'region' => 'Texas']
        ];

        $items = [
            [
                'code' => 'item code',
                'type' => 'shipping',
                'discount_amount' => 2.6
            ],
            [
                'code' => 'another code',
                'type' => 'product',
                'discount_amount' => 5
            ]
        ];

        $taxClassKeyId = [
            'type' => 'id',
            'value' => 1,
        ];

        $data = [
            'data1' => [
                QuoteDetails::KEY_BILLING_ADDRESS => $addressData,
                QuoteDetails::KEY_CUSTOMER_TAX_CLASS_KEY => $taxClassKeyId,
                QuoteDetails::KEY_CUSTOMER_ID => 1
            ],
            'data2' => [
                QuoteDetails::KEY_SHIPPING_ADDRESS => $addressData,
                QuoteDetails::KEY_ITEMS => $items
            ],
            'dataMerged' => [
                QuoteDetails::KEY_BILLING_ADDRESS => $addressData,
                QuoteDetails::KEY_SHIPPING_ADDRESS => $addressData,
                QuoteDetails::KEY_CUSTOMER_ID => 1,
                QuoteDetails::KEY_CUSTOMER_TAX_CLASS_KEY => $taxClassKeyId,
                QuoteDetails::KEY_ITEMS => $items
            ]
        ];

        return $data;
    }
}
