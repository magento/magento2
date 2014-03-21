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
namespace Magento\Customer\Service\V1\Data;

use Magento\TestFramework\Helper\Bootstrap;

class CustomerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder */
    protected $_customerBuilder;

    protected function setUp()
    {
        $this->_customerBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Service\V1\Data\CustomerBuilder'
        );
        parent::setUp();
    }

    /**
     * Two custom attributes are created, one for customer and another for customer address.
     *
     * Attribute related to customer should be returned only.
     *
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_address.php
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_customer.php
     */
    public function testGetCustomAttributesCodes()
    {
        $userDefinedAttributeCode = FIXTURE_ATTRIBUTE_USER_DEFINED_CUSTOMER_NAME;
        $attributeCodes = $this->_customerBuilder->getCustomAttributesCodes();
        $expectedAttributes = array(
            'disable_auto_group_change',
            'prefix',
            'middlename',
            'suffix',
            'created_at',
            'dob',
            'taxvat',
            'gender',
            $userDefinedAttributeCode
        );
        $this->assertEquals($expectedAttributes, $attributeCodes, 'Custom attribute codes list is invalid.');
    }
}
