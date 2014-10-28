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
namespace Magento\Weee\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Service\V1\Data\CustomerBuilder;
use Magento\Customer\Service\V1\Data\Customer;

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

    protected function setUp()
    {
        $weeeConfig = $this->getMock('Magento\Weee\Model\Config', [], [], '', false);
        $weeeConfig->expects($this->any())->method('isEnabled')->will($this->returnValue(true));
        $attribute = $this->getMock('Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $attribute->expects($this->any())->method('getAttributeCodesByFrontendType')->will(
            $this->returnValue(['price'])
        );
        $attributeFactory = $this->getMock('Magento\Eav\Model\Entity\AttributeFactory', [], [], '', false);
        $attributeFactory->expects($this->any())->method('create')->will($this->returnValue($attribute));
        $this->_model = Bootstrap::getObjectManager()->create(
            'Magento\Weee\Model\Tax',
            ['weeeConfig' => $weeeConfig, 'attributeFactory' => $attributeFactory]
        );
    }

    public function testGetProductWeeeAttributes()
    {
        $customerAccountService = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );
        $customerMetadataService = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Service\V1\CustomerMetadataService'
        );
        $customerBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Service\V1\Data\CustomerBuilder',
            ['metadataService' => $customerMetadataService]
        );
        $expected = \Magento\Framework\Service\ExtensibleDataObjectConverter::toFlatArray(
            $customerAccountService->getCustomer(1)
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
        $quote->setCustomerData($customerDataSet);
        $shipping = new \Magento\Framework\Object([
            'quote' =>  $quote
        ]);
        $product = Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
        $product->load(1);
        $weeeTax = Bootstrap::getObjectManager()->create('Magento\Weee\Model\Tax');
        $weeeTaxData = array(
            'website_id' => '1',
            'entity_id' => '1',
            'country' => 'US',
            'value' => '12.4',
            'state' => '0',
            'attribute_id' => '73',
            'entity_type_id' => '0'
        );
        $weeeTax->setData($weeeTaxData);
        $weeeTax->save();
        $amount = $this->_model->getProductWeeeAttributes($product, $shipping);
        $this->assertEquals('12.4000', $amount[0]->getAmount());
    }
}
