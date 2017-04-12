<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for \Magento\Eav\Model\Validator\Attribute\Backend
 */
namespace Magento\Eav\Model\Validator\Attribute;

class BackendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Validator\Attribute\Backend
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Eav\Model\Validator\Attribute\Backend();
    }

    /**
     * Test method for \Magento\Eav\Model\Validator\Attribute\Backend::isValid
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testIsValid()
    {
        /** @var $entity \Magento\Customer\Model\Customer */
        $entity = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Customer::class
        )->load(
            1
        );

        $this->assertTrue($this->_model->isValid($entity));
        $this->assertEmpty($this->_model->getMessages());

        $entity->setData('email', null);
        $this->assertFalse($this->_model->isValid($entity));
        $this->assertArrayHasKey('email', $this->_model->getMessages());

        $entity->setData('firstname', null);
        $this->assertFalse($this->_model->isValid($entity));
        $this->assertArrayHasKey('email', $this->_model->getMessages());
        $this->assertArrayHasKey('firstname', $this->_model->getMessages());
    }
}
