<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for \Magento\Eav\Model\Validator\Attribute\Backend
 */
namespace Magento\Eav\Model\Validator\Attribute;

class BackendTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\Validator\Attribute\Backend
     */
    protected $_model;

    protected function setUp(): void
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

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $reflection = new \ReflectionObject($this);
        foreach ($reflection->getProperties() as $property) {
            if (!$property->isStatic() && 0 !== strpos($property->getDeclaringClass()->getName(), 'PHPUnit')) {
                $property->setAccessible(true);
                $property->setValue($this, null);
            }
        }
    }
}
