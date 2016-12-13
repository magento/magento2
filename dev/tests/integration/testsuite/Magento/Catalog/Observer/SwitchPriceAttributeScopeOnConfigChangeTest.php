<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Observer;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SwitchPriceAttributeScopeOnConfigChangeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testPriceAttributeHasScopeGlobal()
    {
        foreach (['price', 'cost', 'special_price'] as $attributeCode) {
            $attribute = $this->objectManager->get(\Magento\Eav\Model\Config::class)->getAttribute(
                'catalog_product',
                $attributeCode
            );
            $this->assertTrue($attribute->isScopeGlobal());
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testPriceAttributeHasScopeWebsite()
    {
        /** @var ReinitableConfigInterface $config */
        $config = $this->objectManager->get(
            ReinitableConfigInterface::class
        );
        $config->setValue(
            \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
            \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );

        $eventManager = $this->objectManager->get(\Magento\Framework\Event\ManagerInterface::class);
        $eventManager->dispatch(
            "admin_system_config_changed_section_catalog",
            ['website' => 0, 'store' => 0]
        );
        foreach (['price', 'cost', 'special_price'] as $attributeCode) {
            $attribute = $this->objectManager->get(\Magento\Eav\Model\Config::class)->getAttribute(
                'catalog_product',
                $attributeCode
            );
            $this->assertTrue($attribute->isScopeWebsite());
        }
    }
}
