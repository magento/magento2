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
namespace Magento\Customer\Model\Metadata;

use Magento\TestFramework\Helper\Bootstrap;

class FormFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var array */
    private $_requestData;

    /** @var array */
    private $_expectedData;

    public function setUp()
    {
        $this->_requestData = array(
            'id' => 13,
            'default_shipping' => true,
            'default_billing' => false,
            'company' => 'eBay Inc.',
            'fax' => '(444) 444-4444',
            'middlename' => 'MiddleName',
            'prefix' => 'Mr.',
            'suffix' => 'Esq.',
            'vat_id' => 'S46',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'street' => array('2211 North First Street'),
            'city' => 'San Jose',
            'country_id' => 'US',
            'postcode' => '95131',
            'telephone' => '5135135135',
            'region_id' => 12,
            'region' => 'California'
        );

        $this->_expectedData = $this->_requestData;

        unset($this->_expectedData['id']);
        unset($this->_expectedData['default_shipping']);
        unset($this->_expectedData['default_billing']);
        unset($this->_expectedData['middlename']);
        unset($this->_expectedData['prefix']);
        unset($this->_expectedData['suffix']);
    }

    public function testCreate()
    {
        /** @var FormFactory $formFactory */
        $formFactory = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Metadata\FormFactory');
        $form = $formFactory->create('customer_address', 'customer_address_edit');

        $this->assertInstanceOf('\Magento\Customer\Model\Metadata\Form', $form);
        $this->assertNotEmpty($form->getAttributes());

        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = Bootstrap::getObjectManager()->get('Magento\Framework\App\RequestInterface');
        $request->setParams($this->_requestData);

        $this->assertEquals($this->_expectedData, $form->restoreData($form->extractData($request)));
    }
}
