<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Form;

use Magento\Customer\Block\DataProviders\AddressAttributeData;
use Magento\Customer\ViewModel\Address\RegionProvider;
use Magento\Framework\View\Element\Template;
use Magento\TestFramework\Helper\Bootstrap;

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
     * @return void
     */
    public function testCompanyDefault(): void
    {
        /** @var \Magento\Customer\Block\Widget\Company $block */
        $block = Bootstrap::getObjectManager()->create(Register::class)
            ->setTemplate('Magento_Customer::form/register.phtml')
            ->setShowAddressFields(true);
        $this->setAttributeDataProvider($block);
        $this->setRegionProvider($block);

        $this->assertStringContainsString('title="Company"', $block->toHtml());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testTelephoneDefault(): void
    {
        /** @var \Magento\Customer\Block\Widget\Company $block */
        $block = Bootstrap::getObjectManager()->create(
            Register::class
        )->setTemplate('Magento_Customer::form/register.phtml')
        ->setShowAddressFields(true);
        $this->setAttributeDataProvider($block);
        $this->setRegionProvider($block);

        $this->assertStringContainsString('title="Phone&#x20;Number"', $block->toHtml());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testFaxDefault(): void
    {
        /** @var \Magento\Customer\Block\Widget\Company $block */
        $block = Bootstrap::getObjectManager()->create(
            Register::class
        )->setTemplate('Magento_Customer::form/register.phtml')
        ->setShowAddressFields(true);
        $this->setAttributeDataProvider($block);
        $this->setRegionProvider($block);

        $this->assertStringNotContainsString('title="Fax"', $block->toHtml());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testCompanyDisabled(): void
    {
        /** @var \Magento\Customer\Model\Attribute $model */
        $model = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Attribute::class
        );
        $model->loadByCode('customer_address', 'company')->setIsVisible('0');
        $model->save();

        /** @var \Magento\Customer\Block\Widget\Company $block */
        $block = Bootstrap::getObjectManager()->create(
            Register::class
        )->setTemplate('Magento_Customer::form/register.phtml')
        ->setShowAddressFields(true);
        $this->setAttributeDataProvider($block);
        $this->setRegionProvider($block);

        $this->assertStringNotContainsString('title="Company"', $block->toHtml());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testTelephoneDisabled(): void
    {
        /** @var \Magento\Customer\Model\Attribute $model */
        $model = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Attribute::class
        );
        $model->loadByCode('customer_address', 'telephone')->setIsVisible('0');
        $model->save();

        /** @var \Magento\Customer\Block\Widget\Company $block */
        $block = Bootstrap::getObjectManager()->create(
            Register::class
        )->setTemplate('Magento_Customer::form/register.phtml')
        ->setShowAddressFields(true);
        $this->setAttributeDataProvider($block);
        $this->setRegionProvider($block);

        $this->assertStringNotContainsString('title="Phone&#x20;Number"', $block->toHtml());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testFaxEnabled(): void
    {
        /** @var \Magento\Customer\Model\Attribute $model */
        $model = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Attribute::class
        );
        $model->loadByCode('customer_address', 'fax')->setIsVisible('1');
        $model->save();

        /** @var \Magento\Customer\Block\Widget\Company $block */
        $block = Bootstrap::getObjectManager()->create(
            Register::class
        )->setTemplate('Magento_Customer::form/register.phtml')
        ->setShowAddressFields(true);
        $this->setAttributeDataProvider($block);
        $this->setRegionProvider($block);

        $this->assertStringContainsString('title="Fax"', $block->toHtml());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/attribute_city_store_label_address.php
     */
    public function testCityWithStoreLabel(): void
    {
        /** @var \Magento\Customer\Block\Form\Register $block */
        $block = Bootstrap::getObjectManager()->create(
            Register::class
        )->setTemplate('Magento_Customer::form/register.phtml')
            ->setShowAddressFields(true);
        $this->setAttributeDataProvider($block);
        $this->setRegionProvider($block);

        $this->assertStringNotContainsString('title="City"', $block->toHtml());
        $this->assertStringContainsString('title="Suburb"', $block->toHtml());
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        /** @var \Magento\Eav\Model\Config $eavConfig */
        $eavConfig = Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
        $eavConfig->clear();
    }

    /**
     * Set attribute data provider.
     *
     * @param Template $block
     * @return void
     */
    private function setAttributeDataProvider(Template $block): void
    {
        $attributeData = Bootstrap::getObjectManager()->get(AddressAttributeData::class);
        $block->setAttributeData($attributeData);
    }

    /**
     * Set Region Provider View Model.
     *
     * @param Template $block
     * @return void
     */
    private function setRegionProvider(Template $block): void
    {
        $regionProvider = Bootstrap::getObjectManager()->create(RegionProvider::class);
        $block->setRegionProvider($regionProvider);
    }
}
