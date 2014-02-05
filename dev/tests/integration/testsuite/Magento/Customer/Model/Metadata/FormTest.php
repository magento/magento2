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
 * @category    Magento
 * @package     Magento_Customer
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Model\Metadata;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $_formFactory;

    /**
     * @var array
     */
    protected $_attributes = [];

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /** @var array */
    protected $_expected = [];

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_formFactory = $objectManager
            ->create('Magento\Customer\Model\Metadata\FormFactory');

        $this->_requestData = [
            'id' => 14,
            'default_shipping' => true,
            'default_billing' => false,
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
            'region_id' => 0,
            'region' => 'Texas',
        ];

        $this->_expected = $this->_requestData;
        /** Unset data which is not part of the form */
        unset($this->_expected['id']);
        unset($this->_expected['default_shipping']);
        unset($this->_expected['default_billing']);
        unset($this->_expected['middlename']);
        unset($this->_expected['prefix']);
        unset($this->_expected['suffix']);

        $this->_request = $objectManager->get('Magento\App\RequestInterface');
        $this->_request->setParams($this->_requestData);
    }

    public function testCompactData()
    {
        $addressForm = $this->_formFactory->create(
            'customer_address',
            'customer_address_edit',
            []
        );
        $addressData = $addressForm->extractData($this->_request);
        $attributeValues = $addressForm->compactData($addressData);
        $this->assertEquals($this->_expected, $attributeValues);
    }

    public function testGetAttributes()
    {
        $expectedAttributes = [
            'prefix', 'firstname', 'middlename', 'lastname', 'suffix', 'company', 'street', 'city', 'country_id',
            'region', 'region_id', 'postcode', 'telephone', 'fax', 'vat_id'
        ];
        $addressForm = $this->_formFactory->create(
            'customer_address',
            'customer_address_edit',
            []
        );
        $this->assertEquals($expectedAttributes, array_keys($addressForm->getAttributes()));
    }

    public function testGetSystemAttributes()
    {
        $addressForm = $this->_formFactory->create(
            'customer_address',
            'customer_address_edit',
            []
        );
        $this->assertCount(15, $addressForm->getSystemAttributes());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined.php
     */
    public function testGetUserAttributes()
    {
        $expectedAttributes = ['user_attribute'];
        $addressForm = $this->_formFactory->create(
            'customer_address',
            'customer_address_edit',
            []
        );
        $this->assertEquals($expectedAttributes, array_keys($addressForm->getUserAttributes()));
    }
}