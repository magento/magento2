<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Model;

use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDataFixture Magento/Customer/_files/customer_sample.php
 * @magentoDataFixture Magento/Catalog/_files/products.php
 * @magentoDataFixture Magento/Weee/_files/product_with_fpt.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Weee\Model\Tax
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    private $_extensibleDataObjectConverter;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $weeeConfig = $this->createMock(\Magento\Weee\Model\Config::class);
        $weeeConfig->expects($this->any())->method('isEnabled')->willReturn(true);
        $weeeConfig->expects($this->any())->method('isTaxable')->willReturn(true);
        $attribute = $this->createMock(\Magento\Eav\Model\Entity\Attribute::class);
        $attribute->expects($this->any())->method('getAttributeCodesByFrontendType')->willReturn(
            ['weee']
        );
        $attributeFactory = $this->createPartialMock(\Magento\Eav\Model\Entity\AttributeFactory::class, ['create']);
        $attributeFactory->expects($this->any())->method('create')->willReturn($attribute);
        $this->_model = $objectManager->create(
            \Magento\Weee\Model\Tax::class,
            ['weeeConfig' => $weeeConfig, 'attributeFactory' => $attributeFactory]
        );
        $this->_extensibleDataObjectConverter = $objectManager->get(
            \Magento\Framework\Api\ExtensibleDataObjectConverter::class
        );
    }

    public function testGetProductWeeeAttributes()
    {
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $customerMetadataService = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Api\CustomerMetadataInterface::class
        );
        $customerFactory = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Api\Data\CustomerInterfaceFactory::class,
            ['metadataService' => $customerMetadataService]
        );
        $dataObjectHelper = Bootstrap::getObjectManager()->create(\Magento\Framework\Api\DataObjectHelper::class);
        $expected = $this->_extensibleDataObjectConverter->toFlatArray(
            $customerRepository->getById(1),
            [],
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $customerDataSet = $customerFactory->create();
        $dataObjectHelper->populateWithArray(
            $customerDataSet,
            $expected,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $fixtureGroupCode = 'custom_group';
        $fixtureTaxClassId = 3;
        /** @var \Magento\Customer\Model\Group $group */
        $group = Bootstrap::getObjectManager()->create(\Magento\Customer\Model\Group::class);
        $fixtureGroupId = $group->load($fixtureGroupCode, 'customer_group_code')->getId();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
        $quote->setCustomerGroupId($fixtureGroupId);
        $quote->setCustomerTaxClassId($fixtureTaxClassId);
        $quote->setCustomer($customerDataSet);
        $shipping = new \Magento\Framework\DataObject([
            'quote' =>  $quote,
        ]);
        $productRepository = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $product = $productRepository->get('simple-with-ftp');

        $amount = $this->_model->getProductWeeeAttributes($product, $shipping, null, null, true);
        $this->assertIsArray($amount);
        $this->assertArrayHasKey(0, $amount);
        $this->assertEquals(12.70, $amount[0]->getAmount());
    }
}
