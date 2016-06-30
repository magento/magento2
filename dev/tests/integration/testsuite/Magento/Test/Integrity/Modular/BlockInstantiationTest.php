<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

use Magento\Customer\Model\Context;

/**
 * This test ensures that all blocks have the appropriate constructor arguments that allow
 * them to be instantiated via the objectManager.
 *
 * @magentoAppIsolation enabled
 */
class BlockInstantiationTest extends \Magento\TestFramework\TestCase\AbstractIntegrity
{
    public function testBlockInstantiation()
    {
        $this->ech("++++ starting test 2.0-MIKE testBlockInstantiation()");

        // TODO: used for extreme debugging
        if (false) {
            $this->ech("++++ bypassing entire test!");
            return;
        }

        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            function ($module, $class, $area) {
                $this->ech("Module: " . $module . ", Class: " . $class . ", Area: " . $area);
                $this->assertNotEmpty($module);
                $this->assertTrue(class_exists($class), "Block class: {$class}");
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Framework\Config\ScopeInterface'
                )->setCurrentScope(
                    $area
                );
                $context = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Framework\App\Http\Context'
                );
                $context->setValue(Context::CONTEXT_AUTH, false, false);
                $context->setValue(
                    Context::CONTEXT_GROUP,
                    \Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID,
                    \Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID
                );
                \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea($area);

                try {
                    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create($class);
                } catch (\Exception $e) {
                    $this->ech("Unable to instantiate class: " . $class);
                    throw new \Exception("Unable to instantiate '{$class}'", 0, $e);
                }
            },
            $this->allBlocksDataProvider()
        );
    }

    // TODO: echo out the message
    private function ech($msg) {
        echo $msg . "\n";
        ob_flush();
        flush();
    }

    /**
     * @return array
     */
    public function allBlocksDataProvider()
    {
        $blockClass = '';
        try {
            /** @var $website \Magento\Store\Model\Website */
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Store\Model\StoreManagerInterface'
            )->getStore()->setWebsiteId(
                0
            );

            $ceSkippedModules = $this->getCEModulesToSkip(); // TODO: debug
            $eeSkippedModules = $this->getEEModulesToSkip(); // TODO: debug

            $enabledModules = $this->_getEnabledModules();
            $skipBlocks = $this->_getBlocksToSkip();
            $templateBlocks = [];
            $blockMods = \Magento\Framework\App\Utility\Classes::collectModuleClasses('Block');
            foreach ($blockMods as $blockClass => $module) {
                //$this->ech("++>> module: " . $module . ", blockClass: " . $blockClass);
                if (in_array($module, $ceSkippedModules)) {
                    $this->ech("++++>> skipping ~CE~ module: " . $module);
                    continue;
                }
                if (in_array($module, $eeSkippedModules)) {
                    $this->ech("++++>> skipping +EE+ module: " . $module);
                    continue;
                }
                if (!isset($enabledModules[$module]) || isset($skipBlocks[$blockClass])) {
                    continue;
                }
                $class = new \ReflectionClass($blockClass);
                if ($class->isAbstract() || !$class->isSubclassOf('Magento\Framework\View\Element\Template')) {
                    continue;
                }
                $templateBlocks = $this->_addBlock($module, $blockClass, $class, $templateBlocks);
            }
            return $templateBlocks;
        } catch (\Exception $e) {
            $this->ech("Corrupted data provider. Last know block: " . $blockClass);
            trigger_error(
                "Corrupted data provider. Last known block instantiation attempt: '{$blockClass}'." .
                " Exception: {$e}",
                E_USER_ERROR
            );
        }
    }

    /**
     * TODO: debug CE contribution to EE build break
     *
     * @return array
     */
    protected function getCEModulesToSkip()
    {
        $result = [];$result[] = "AdminNotification";
        $result[] = "AdvancedPricingImportExport";
        $result[] = "Authorization";
        $result[] = "Authorizenet";
        $result[] = "Backend";
        $result[] = "Backup";
        $result[] = "Braintree";
        $result[] = "Bundle";
        $result[] = "BundleImportExport";
        $result[] = "CacheInvalidate";
        $result[] = "Captcha";
        $result[] = "Catalog";
        $result[] = "CatalogImportExport";
        $result[] = "CatalogInventory";
        $result[] = "CatalogRule";
        $result[] = "CatalogRuleConfigurable";
        $result[] = "CatalogSearch";
        $result[] = "CatalogUrlRewrite";
        $result[] = "CatalogWidget";
        $result[] = "Checkout";
        $result[] = "CheckoutAgreements";
        $result[] = "Cms";
        $result[] = "CmsUrlRewrite";
        $result[] = "Config";
        $result[] = "ConfigurableImportExport";
        $result[] = "ConfigurableProduct";
        $result[] = "Contact";
        $result[] = "Cookie";
        $result[] = "Cron";
        $result[] = "CurrencySymbol";
        $result[] = "Customer";
        $result[] = "CustomerImportExport";
        $result[] = "Deploy";
        $result[] = "Developer";
        $result[] = "Dhl";
        $result[] = "Directory";
        $result[] = "Downloadable";
        $result[] = "DownloadableImportExport";
        $result[] = "Eav";
        $result[] = "Email";
        $result[] = "EncryptionKey";
        $result[] = "Fedex";
        $result[] = "GiftMessage";
        $result[] = "GoogleAdwords";
        $result[] = "GoogleAnalytics";
        $result[] = "GoogleOptimizer";
        $result[] = "GroupedImportExport";
        $result[] = "GroupedProduct";
        $result[] = "ImportExport";
        $result[] = "Indexer";
        $result[] = "Integration";
        $result[] = "LayeredNavigation";
        $result[] = "Marketplace";
        $result[] = "MediaStorage";
        $result[] = "Msrp";
        $result[] = "Multishipping";
        $result[] = "NewRelicReporting";
        $result[] = "Newsletter";
        $result[] = "OfflinePayments";
        $result[] = "OfflineShipping";
        $result[] = "PageCache";
        $result[] = "Payment";
        $result[] = "Paypal";
        $result[] = "Persistent";
        $result[] = "ProductAlert";
        $result[] = "ProductVideo";
        $result[] = "Quote";
        $result[] = "Reports";
        $result[] = "RequireJs";
        $result[] = "Review";
        $result[] = "Rss";
        $result[] = "Rule";
        $result[] = "Sales";
        $result[] = "SalesRule";
        $result[] = "SalesSequence";
        $result[] = "SampleData";
        $result[] = "Search";
        $result[] = "SendFriend";
        $result[] = "Shipping";
        $result[] = "Sitemap";
        $result[] = "Store";
        $result[] = "Swagger";
        $result[] = "Swatches";
        $result[] = "Tax";
        $result[] = "TaxImportExport";
        $result[] = "TestModule1";
        $result[] = "TestModule2";
        $result[] = "TestModule3";
        $result[] = "TestModule4";
        $result[] = "TestModule5";
        $result[] = "TestModuleIntegrationFromConfig";
        $result[] = "TestModuleJoinDirectives";
        $result[] = "TestModuleMSC";
        $result[] = "Theme";
        $result[] = "Translation";
        $result[] = "Ui";
        $result[] = "Ups";
        $result[] = "UrlRewrite";
        $result[] = "User";
        $result[] = "Usps";
        $result[] = "Variable";
        $result[] = "Version";
        $result[] = "Webapi";
        $result[] = "WebapiSecurity";
        $result[] = "Weee";
        $result[] = "Widget";
        $result[] = "Wishlist";
        return $result;
    }

    /**
     * TODO: debug EE build break
     *
     * @return array
     */
    protected function getEEModulesToSkip()
    {
        $result = [];
        $result[] = "Magento_AdminGws";
        $result[] = "Magento_AdvancedCatalog";
        $result[] = "Magento_AdvancedCheckout";
        $result[] = "Magento_AdvancedSearch";
        $result[] = "Magento_Amqp";
        $result[] = "Magento_Banner";
        $result[] = "Magento_BannerCustomerSegment";
        $result[] = "Magento_CatalogEvent";
        $result[] = "Magento_CatalogPermissions";
        $result[] = "Magento_CustomAttributeManagement";
        $result[] = "Magento_CustomerBalance";
        $result[] = "Magento_CustomerCustomAttributes";
        $result[] = "Magento_CustomerFinance";
        $result[] = "Magento_CustomerSegment";
        $result[] = "Magento_Cybersource";
        $result[] = "Magento_Doc";
        $result[] = "Magento_Enterprise";
        $result[] = "Magento_Eway";
        $result[] = "Magento_GiftCard";
        $result[] = "Magento_GiftCardAccount";
        $result[] = "Magento_GiftCardImportExport";
        $result[] = "Magento_GiftRegistry";
        $result[] = "Magento_GiftWrapping";
        $result[] = "Magento_GoogleTagManager";
        $result[] = "Magento_Invitation";
        $result[] = "Magento_Logging";
        $result[] = "Magento_MessageQueue";
        $result[] = "Magento_MultipleWishlist";
        $result[] = "Magento_MysqlMq";
        $result[] = "Magento_PersistentHistory";
        $result[] = "Magento_PricePermissions";
        $result[] = "Magento_PromotionPermissions";
        $result[] = "Magento_Reminder";
        $result[] = "Magento_ResourceConnections";
        $result[] = "Magento_Reward";
        $result[] = "Magento_Rma";
        $result[] = "Magento_SalesArchive";
        $result[] = "Magento_ScalableCheckout";
        $result[] = "Magento_ScalableInventory";
        $result[] = "Magento_ScalableOms";
        $result[] = "Magento_ScheduledImportExport";
        $result[] = "Magento_Solr";
        $result[] = "Magento_Support";
        $result[] = "Magento_TargetRule";
        $result[] = "Magento_VersionsCms";
        $result[] = "Magento_VisualMerchandiser";
        $result[] = "Magento_WebsiteRestriction";
        $result[] = "Magento_Worldpay";
        return $result;
    }

    /**
     * Loads block classes, that should not be instantiated during the instantiation test
     *
     * @return array
     */
    protected function _getBlocksToSkip()
    {
        $result = [];
        foreach (glob(__DIR__ . '/_files/skip_blocks*.php') as $file) {
            $blocks = include $file;
            $result = array_merge($result, $blocks);
        }
        return array_combine($result, $result);
    }

    /**
     * @param $module
     * @param $blockClass
     * @param $class
     * @param $templateBlocks
     * @return mixed
     */
    private function _addBlock($module, $blockClass, $class, $templateBlocks)
    {
        $area = 'frontend';
        if ($module == 'Magento_Backend' || strpos(
            $blockClass,
            '\\Adminhtml\\'
        ) || strpos(
            $blockClass,
            '_Backend_'
        ) || $class->isSubclassOf(
            'Magento\Backend\Block\Template'
        )
        ) {
            $area = 'adminhtml';
        }
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\AreaList'
        )->getArea(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        )->load(
            \Magento\Framework\App\Area::PART_CONFIG
        );
        $templateBlocks[$module . ', ' . $blockClass . ', ' . $area] = [$module, $blockClass, $area];
        return $templateBlocks;
    }
}
