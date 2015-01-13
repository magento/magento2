<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model;

use Magento\Customer\Api\Data\CustomerDataBuilder;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDataFixture Magento/Customer/_files/customer_sample.php
 * @magentoDataFixture Magento/Catalog/_files/products.php
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
        $attribute = $this->getMock('Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $attribute->expects($this->any())->method('getAttributeCodesByFrontendType')->will(
            $this->returnValue(['price'])
        );
        $attributeFactory = $this->getMock('Magento\Eav\Model\Entity\AttributeFactory', [], [], '', false);
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
        $customerBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\Data\CustomerDataBuilder',
            ['metadataService' => $customerMetadataService]
        );
        $expected = $this->_extensibleDataObjectConverter->toFlatArray(
            $customerRepository->getById(1), [], '\Magento\Customer\Api\Data\CustomerInterface'
        );
        $customerBuilder->populateWithArray($expected);
        $customerDataSet = $customerBuilder->create();
        $fixtureGroupCode = 'custom_group';
        $fixtureTaxClassId = 3;
        /** @var \Magento\Customer\Model\Group $group */
        $group = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Group');
        $fixtureGroupId = $group->load($fixtureGroupCode, 'customer_group_code')->getId();
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
        $quote->setCustomerGroupId($fixtureGroupId);
        $quote->setCustomerTaxClassId($fixtureTaxClassId);
        $quote->setCustomer($customerDataSet);
        $shipping = new \Magento\Framework\Object([
            'quote' =>  $quote,
        ]);
        $product = Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
        $product->load(1);
        $weeeTax = Bootstrap::getObjectManager()->create('Magento\Weee\Model\Tax');
        $weeeTaxData = [
            'website_id' => '1',
            'entity_id' => '1',
            'country' => 'US',
            'value' => '12.4',
            'state' => '0',
            'attribute_id' => '73',
            'entity_type_id' => '0',
        ];
        $weeeTax->setData($weeeTaxData);
        $weeeTax->save();
        $amount = $this->_model->getProductWeeeAttributes($product, $shipping);
        $this->assertEquals('12.4000', $amount[0]->getAmount());
    }
}
