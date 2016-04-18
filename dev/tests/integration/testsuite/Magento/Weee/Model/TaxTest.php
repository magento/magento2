<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Weee\Model;

use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDataFixture Magento/Customer/_files/customer_sample.php
 * @magentoDataFixture Magento/Catalog/_files/products.php
 * @magentoDataFixture Magento/Weee/_files/product_with_fpt.php
 */
class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Weee\Model\Tax
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    private $_extensibleDataObjectConverter;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $weeeConfig = $this->getMock('Magento\Weee\Model\Config', [], [], '', false);
        $weeeConfig->expects($this->any())->method('isEnabled')->will($this->returnValue(true));
        $weeeConfig->expects($this->any())->method('isTaxable')->will($this->returnValue(true));
        $attribute = $this->getMock('Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $attribute->expects($this->any())->method('getAttributeCodesByFrontendType')->will(
            $this->returnValue(['weee'])
        );
        $attributeFactory = $this->getMock('Magento\Eav\Model\Entity\AttributeFactory', ['create'], [], '', false);
        $attributeFactory->expects($this->any())->method('create')->will($this->returnValue($attribute));
        $this->_model = $objectManager->create(
            'Magento\Weee\Model\Tax',
            ['weeeConfig' => $weeeConfig, 'attributeFactory' => $attributeFactory]
        );
        $this->_extensibleDataObjectConverter = $objectManager->get(
            'Magento\Framework\Api\ExtensibleDataObjectConverter'
        );
    }

    public function testGetProductWeeeAttributes()
    {
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );
        $customerMetadataService = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\CustomerMetadataInterface'
        );
        $customerFactory = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\Data\CustomerInterfaceFactory',
            ['metadataService' => $customerMetadataService]
        );
        $dataObjectHelper = Bootstrap::getObjectManager()->create('Magento\Framework\Api\DataObjectHelper');
        $expected = $this->_extensibleDataObjectConverter->toFlatArray(
            $customerRepository->getById(1), [], '\Magento\Customer\Api\Data\CustomerInterface'
        );
        $customerDataSet = $customerFactory->create();
        $dataObjectHelper->populateWithArray(
            $customerDataSet,
            $expected,
            '\Magento\Customer\Api\Data\CustomerInterface'
        );
        $fixtureGroupCode = 'custom_group';
        $fixtureTaxClassId = 3;
        /** @var \Magento\Customer\Model\Group $group */
        $group = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Group');
        $fixtureGroupId = $group->load($fixtureGroupCode, 'customer_group_code')->getId();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = Bootstrap::getObjectManager()->create('Magento\Quote\Model\Quote');
        $quote->setCustomerGroupId($fixtureGroupId);
        $quote->setCustomerTaxClassId($fixtureTaxClassId);
        $quote->setCustomer($customerDataSet);
        $shipping = new \Magento\Framework\DataObject([
            'quote' =>  $quote,
        ]);
        $product = Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
        $product->load(101);

        $amount = $this->_model->getProductWeeeAttributes($product, $shipping, null, null, true);
        $this->assertTrue(is_array($amount));
        $this->assertArrayHasKey(0, $amount);
        $this->assertEquals(12.70, $amount[0]->getAmount());
    }
}
