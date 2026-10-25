<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Observer;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Indexer\TestCase;
use Magento\Theme\Api\DesignConfigRepositoryInterface;
use Magento\Theme\Test\Fixture\DesignConfig as DesignConfigFixture;

class MoveStoreLevelDesignConfigToWebsiteScopeOnSingleStoreModeTest extends TestCase
{
    private const XML_PATH_DESIGN_FOOTER_ABSOLUTE_FOOTER = 'design/footer/absolute_footer';
    private const INITIAL_FOOTER_TEXT_STORES = 'test footer text for store scope';
    private const INITIAL_FOOTER_TEXT_WEBSITES = 'test footer text for websites scope';
    private const UPDATED_FOOTER_TEXT_WEBSITES = 'updated footer text for websites scope';

    #[
        DataFixture(
            DesignConfigFixture::class,
            [
                'scope_type' => ScopeInterface::SCOPE_WEBSITES,
                'scope_id' => 1,
                'data' => [
                    [
                        'path' => self::XML_PATH_DESIGN_FOOTER_ABSOLUTE_FOOTER,
                        'value' => self::INITIAL_FOOTER_TEXT_WEBSITES
                    ]
                ]
            ]
        ),
        DataFixture(
            DesignConfigFixture::class,
            [
                'scope_type' => ScopeInterface::SCOPE_STORES,
                'scope_id' => 1,
                'data' => [
                    [
                        'path' => self::XML_PATH_DESIGN_FOOTER_ABSOLUTE_FOOTER,
                        'value' => self::INITIAL_FOOTER_TEXT_STORES
                    ]
                ]
            ]
        ),
        Config(StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED, 1),
        AppArea('adminhtml')
    ]
    public function testExecute(): void
    {
        $eventManager = Bootstrap::getObjectManager()->get(ManagerInterface::class);
        $scopeConfig = Bootstrap::getObjectManager()->get(ReinitableConfigInterface::class);
        $this->assertEquals(
            self::INITIAL_FOOTER_TEXT_WEBSITES,
            $scopeConfig->getValue(self::XML_PATH_DESIGN_FOOTER_ABSOLUTE_FOOTER, ScopeInterface::SCOPE_WEBSITES)
        );
        $this->assertEquals(
            self::INITIAL_FOOTER_TEXT_STORES,
            $scopeConfig->getValue(self::XML_PATH_DESIGN_FOOTER_ABSOLUTE_FOOTER, ScopeInterface::SCOPE_STORES)
        );
        $eventManager->dispatch(
            'admin_system_config_changed_section_general',
            [
                'website' => '',
                'store' => '',
                'changed_paths' => [
                    StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED
                ],
            ]
        );
        $this->assertEquals(
            self::INITIAL_FOOTER_TEXT_STORES,
            $scopeConfig->getValue(self::XML_PATH_DESIGN_FOOTER_ABSOLUTE_FOOTER, ScopeInterface::SCOPE_WEBSITES)
        );
        $this->assertEquals(
            self::INITIAL_FOOTER_TEXT_STORES,
            $scopeConfig->getValue(self::XML_PATH_DESIGN_FOOTER_ABSOLUTE_FOOTER, ScopeInterface::SCOPE_STORES)
        );

        $this->updateConfig(
            self::XML_PATH_DESIGN_FOOTER_ABSOLUTE_FOOTER,
            self::UPDATED_FOOTER_TEXT_WEBSITES,
            ScopeInterface::SCOPE_WEBSITES,
            1
        );

        $this->assertEquals(
            self::UPDATED_FOOTER_TEXT_WEBSITES,
            $scopeConfig->getValue(self::XML_PATH_DESIGN_FOOTER_ABSOLUTE_FOOTER, ScopeInterface::SCOPE_WEBSITES)
        );
        $this->assertEquals(
            self::UPDATED_FOOTER_TEXT_WEBSITES,
            $scopeConfig->getValue(self::XML_PATH_DESIGN_FOOTER_ABSOLUTE_FOOTER, ScopeInterface::SCOPE_STORES)
        );
    }

    private function updateConfig(string $path, string $value, string $scopeType, int $scopeId): void
    {
        $designConfigRepository = Bootstrap::getObjectManager()->get(DesignConfigRepositoryInterface::class);
        $designConfig = $designConfigRepository->getByScope($scopeType, $scopeId);
        $fieldsData = $designConfig->getExtensionAttributes()->getDesignConfigData();
        foreach ($fieldsData as $fieldData) {
            if ($fieldData->getPath() === $path) {
                $fieldData->setValue($value);
            }

        }
        $designConfig->setScope($scopeType);
        $designConfig->setScopeId($scopeId);
        $designConfigRepository->save($designConfig);
    }
}
