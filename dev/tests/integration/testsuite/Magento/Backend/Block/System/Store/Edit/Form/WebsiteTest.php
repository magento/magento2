<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\System\Store\Edit\Form;

use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\Website;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppIsolation enabled
 * @magentoAppArea adminhtml
 */
class WebsiteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\System\Store\Edit\Form\Website
     */
    protected $_block;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $registryData = [
            'store_type' => 'website',
            'store_data' => $objectManager->create(\Magento\Store\Model\Website::class),
            'store_action' => 'add',
        ];
        foreach ($registryData as $key => $value) {
            $objectManager->get(\Magento\Framework\Registry::class)->register($key, $value);
        }

        /** @var $layout \Magento\Framework\View\Layout */
        $layout = $objectManager->get(\Magento\Framework\View\LayoutInterface::class);

        $this->_block = $layout->createBlock(\Magento\Backend\Block\System\Store\Edit\Form\Website::class);

        $this->_block->toHtml();
    }

    protected function tearDown(): void
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)->unregister('store_type');
        $objectManager->get(\Magento\Framework\Registry::class)->unregister('store_data');
        $objectManager->get(\Magento\Framework\Registry::class)->unregister('store_action');
    }

    public function testPrepareForm()
    {
        $form = $this->_block->getForm();
        $this->assertEquals('website_fieldset', $form->getElement('website_fieldset')->getId());
        $this->assertEquals('website_name', $form->getElement('website_name')->getId());
        $this->assertEquals('website', $form->getElement('store_type')->getValue());
    }

    #[
        DataFixture(WebsiteFixture::class, as: 'website')
    ]
    public function testDefaultStoreFieldIsAbsentForWebsiteWithoutStoreGroups()
    {
        $form = $this->renderEditForm('website');

        $this->assertNull($form->getElement('website_default_group_id'));
        $this->assertNotNull($form->getElement('website_name'));
    }

    #[
        DataFixture(WebsiteFixture::class, as: 'website'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website.id$'], 'store_group')
    ]
    public function testDefaultStoreFieldIsPresentForWebsiteWithStoreGroups()
    {
        $form = $this->renderEditForm('website');

        $field = $form->getElement('website_default_group_id');
        $this->assertNotNull($field);
        $this->assertNotEmpty($field->getValues());
    }

    private function renderEditForm(string $websiteAlias): \Magento\Framework\Data\Form
    {
        $objectManager = Bootstrap::getObjectManager();
        $registry = $objectManager->get(Registry::class);
        $website = $objectManager->create(Website::class)
            ->load(DataFixtureStorageManager::getStorage()->get($websiteAlias)->getId());
        $registry->unregister('store_data');
        $registry->unregister('store_action');
        $registry->register('store_data', $website);
        $registry->register('store_action', 'edit');

        $block = $objectManager->get(LayoutInterface::class)
            ->createBlock(\Magento\Backend\Block\System\Store\Edit\Form\Website::class);
        $block->toHtml();

        return $block->getForm();
    }
}
