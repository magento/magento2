<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->_requestData = [
            'id' => 13,
            'default_shipping' => true,
            'default_billing' => false,
            'company' => 'Magento Commerce Inc.',
            'middlename' => 'MiddleName',
            'prefix' => 'Mr.',
            'suffix' => 'Esq.',
            'vat_id' => 'S46',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'street' => ['2211 North First Street'],
            'city' => 'San Jose',
            'country_id' => 'US',
            'postcode' => '95131',
            'telephone' => '5135135135',
            'region_id' => 12,
            'region' => 'California',
        ];

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
        $formFactory = Bootstrap::getObjectManager()->create(\Magento\Customer\Model\Metadata\FormFactory::class);
        $form = $formFactory->create('customer_address', 'customer_address_edit');

        $this->assertInstanceOf(\Magento\Customer\Model\Metadata\Form::class, $form);
        $this->assertNotEmpty($form->getAttributes());

        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = Bootstrap::getObjectManager()->get(\Magento\Framework\App\RequestInterface::class);
        $request->setParams($this->_requestData);

        $this->assertEquals($this->_expectedData, $form->restoreData($form->extractData($request)));
    }
}
