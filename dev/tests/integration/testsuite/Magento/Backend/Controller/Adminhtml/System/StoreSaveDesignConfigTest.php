<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Controller\Adminhtml\System;

use Magento\Backend\Controller\Adminhtml\System\Store\Save as StoreSaveController;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\DataObject;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\Group;
use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\Store\Test\Fixture\Group as GroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory as ThemeCollectionFactory;
use Magento\Theme\Test\Fixture\DesignConfig as DesignConfigFixture;

/**
 * Integration coverage for store view code changes with design configuration (scoped theme).
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreSaveDesignConfigTest extends AbstractBackendController
{
    private const WEBSITE_CODE = 'design_cfg_test_ws';

    private const GROUP_CODE = 'design_cfg_test_grp';

    private const ORIGINAL_STORE_CODE = 'design_cfg_test_sv';

    private const RENAMED_STORE_CODE = 'design_cfg_test_sv_rn';

    /**
     * After renaming a store view code, scoped design/theme configuration must still resolve for the new code.
     *
     * @return void
     */
    #[
        DataFixture(
            WebsiteFixture::class,
            [
                'code' => self::WEBSITE_CODE,
                'name' => 'Design Config Test Website',
                'is_default' => '0',
            ],
            'des_ws'
        ),
        DataFixture(
            GroupFixture::class,
            [
                'code' => self::GROUP_CODE,
                'name' => 'Design Config Test Store Group',
                'website_id' => '$des_ws.id$',
            ],
            'des_grp'
        ),
        DataFixture(
            StoreFixture::class,
            [
                'code' => self::ORIGINAL_STORE_CODE,
                'name' => 'Design Config Test Store View',
                'sort_order' => '10',
                'is_active' => '1',
                'website_id' => '$des_ws.id$',
                'store_group_id' => '$des_grp.id$',
            ],
            'des_store'
        ),
        DbIsolation(false),
        AppArea('adminhtml'),
    ]
    public function testDesignThemeConfigRemainsAfterStoreViewCodeChange(): void
    {
        $designConfigState = null;
        try {
            $formKey = $this->_objectManager->get(FormKey::class);
            $scopeConfig = $this->_objectManager->get(ScopeConfigInterface::class);
            $storeManager = $this->_objectManager->get(StoreManagerInterface::class);
            $themeCollectionFactory = $this->_objectManager->get(ThemeCollectionFactory::class);
            $storeResource = $this->_objectManager->get(StoreResource::class);
            $typeList = $this->_objectManager->get(TypeListInterface::class);
            $reinitableConfig = $this->_objectManager->get(ReinitableConfigInterface::class);
            $designConfigFixture = $this->_objectManager->create(DesignConfigFixture::class);

            $themeCollection = $themeCollectionFactory->create();
            $lumaThemeId = (int) $themeCollection->getThemeByFullPath('frontend/Magento/luma')->getId();

            /** @var Store $store */
            $store = $this->_objectManager->create(Store::class);
            $storeResource->load($store, self::ORIGINAL_STORE_CODE, 'code');
            $this->assertNotEmpty($store->getId(), 'Fixture store view should exist');

            $designConfigState = $designConfigFixture->apply(
                [
                    'scope_type' => ScopeInterface::SCOPE_STORES,
                    'scope_id' => (int) $store->getId(),
                    'data' => [
                        [
                            'path' => DesignInterface::XML_PATH_THEME_ID,
                            'value' => (string) $lumaThemeId,
                        ],
                    ],
                ]
            );
            $typeList->cleanType('config');
            $reinitableConfig->reinit();
            $storeManager->reinitStores();

            $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
            $this->getRequest()->setPostValue([
                'form_key' => $formKey->getFormKey(),
                'store_type' => 'store',
                'store_action' => 'edit',
                'store' => [
                    'store_id' => (string) $store->getId(),
                    'name' => $store->getName(),
                    'code' => self::RENAMED_STORE_CODE,
                    'is_active' => $store->isActive() ? '1' : '0',
                    'sort_order' => (string) $store->getSortOrder(),
                    'is_default' => $store->isDefault() ? '1' : '0',
                    'group_id' => (string) $store->getGroupId(),
                ],
            ]);
            // Run the controller without Http::launch() so frontend layout (e.g. B2B Reorder Sidebar plugin
            // using admin user id as customer id) is not rendered.
            $request = $this->getRequest();
            $request->setDispatched(true);
            $saveAction = $this->_objectManager->create(StoreSaveController::class);
            $saveAction->dispatch($request);

            $this->assertSessionMessages(
                $this->containsEqual((string) __('You saved the store view.')),
                MessageInterface::TYPE_SUCCESS,
                ManagerInterface::class
            );

            $storeManager->reinitStores();
            $reinitableConfig->reinit();

            $themeIdAfterRename = (int) $scopeConfig->getValue(
                DesignInterface::XML_PATH_THEME_ID,
                ScopeInterface::SCOPE_STORE,
                self::RENAMED_STORE_CODE
            );
            $this->assertSame(
                $lumaThemeId,
                $themeIdAfterRename,
                'Design theme for the store view must stay correct after the store code is changed'
            );
        } finally {
            $this->cleanupStoreDesignTestData($designConfigState);
            $this->resetRequest();
        }
    }

    /**
     * Revert design config and remove website hierarchy (core Store revert cannot find row after code rename).
     *
     * @param DataObject|null $designConfigState
     * @return void
     */
    private function cleanupStoreDesignTestData(?DataObject $designConfigState): void
    {
        $om = $this->_objectManager;
        if ($designConfigState instanceof DataObject) {
            $om->create(DesignConfigFixture::class)->revert($designConfigState);
            $om->get(TypeListInterface::class)->cleanType('config');
            $om->get(ReinitableConfigInterface::class)->reinit();
        }

        $registry = $om->get(Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        try {
            $storeResource = $om->get(StoreResource::class);
            $store = $om->create(Store::class);
            $fixtureStore = DataFixtureStorageManager::getStorage()->get('des_store');
            if ($fixtureStore && $fixtureStore->getId()) {
                $storeResource->load($store, (int) $fixtureStore->getId());
                if ($store->getId()) {
                    $storeResource->delete($store);
                }
            }
            foreach ([self::RENAMED_STORE_CODE, self::ORIGINAL_STORE_CODE] as $storeCode) {
                $storeResource->load($store, $storeCode, 'code');
                if ($store->getId()) {
                    $storeResource->delete($store);
                }
            }

            $groupResource = $om->get(GroupResource::class);
            $group = $om->create(Group::class);
            $groupResource->load($group, self::GROUP_CODE, 'code');
            if ($group->getId()) {
                $groupResource->delete($group);
            }

            $websiteResource = $om->get(WebsiteResource::class);
            $website = $om->create(Website::class);
            $websiteResource->load($website, self::WEBSITE_CODE, 'code');
            if ($website->getId()) {
                $websiteResource->delete($website);
            }
        } finally {
            $registry->unregister('isSecureArea');
            $registry->register('isSecureArea', false);
            $om->get(StoreManagerInterface::class)->reinitStores();
        }
    }
}
