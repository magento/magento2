<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Form;

/**
 * Test class for \Magento\Customer\Block\Form\Register
 *
 * @magentoAppArea frontend
 */
class RegisterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testCompanyDefault()
    {
        /** @var \Magento\Customer\Block\Widget\Company $block */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Block\Form\Register::class
        )->setTemplate('Magento_Customer::form/register.phtml')
        ->setShowAddressFields(true);

        $this->assertContains('title="Company"', $block->toHtml());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testTelephoneDefault()
    {
        /** @var \Magento\Customer\Block\Widget\Company $block */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Block\Form\Register::class
        )->setTemplate('Magento_Customer::form/register.phtml')
        ->setShowAddressFields(true);

        $this->assertContains('title="Phone&#x20;Number"', $block->toHtml());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testFaxDefault()
    {
        /** @var \Magento\Customer\Block\Widget\Company $block */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Block\Form\Register::class
        )->setTemplate('Magento_Customer::form/register.phtml')
        ->setShowAddressFields(true);

        $this->assertNotContains('title="Fax"', $block->toHtml());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testCompanyDisabled()
    {
        /** @var \Magento\Customer\Model\Attribute $model */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Attribute::class
        );
        $model->loadByCode('customer_address', 'company')->setIsVisible('0');
        $model->save();

        /** @var \Magento\Customer\Block\Widget\Company $block */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Block\Form\Register::class
        )->setTemplate('Magento_Customer::form/register.phtml')
        ->setShowAddressFields(true);

        $this->assertNotContains('title="Company"', $block->toHtml());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testTelephoneDisabled()
    {
        /** @var \Magento\Customer\Model\Attribute $model */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Attribute::class
        );
        $model->loadByCode('customer_address', 'telephone')->setIsVisible('0');
        $model->save();

        /** @var \Magento\Customer\Block\Widget\Company $block */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Block\Form\Register::class
        )->setTemplate('Magento_Customer::form/register.phtml')
        ->setShowAddressFields(true);

        $this->assertNotContains('title="Phone&#x20;Number"', $block->toHtml());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testFaxEnabled()
    {
        /** @var \Magento\Customer\Model\Attribute $model */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Attribute::class
        );
        $model->loadByCode('customer_address', 'fax')->setIsVisible('1');
        $model->save();

        /** @var \Magento\Customer\Block\Widget\Company $block */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Block\Form\Register::class
        )->setTemplate('Magento_Customer::form/register.phtml')
        ->setShowAddressFields(true);

        $this->assertContains('title="Fax"', $block->toHtml());
    }

    protected function tearDown()
    {
        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
        $eavConfig->clear();
    }
}
