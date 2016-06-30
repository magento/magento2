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
        $result = [];
//        $result[] = "Magento_AdminNotification";
//        $result[] = "Magento_AdvancedPricingImportExport";
//        $result[] = "Magento_Authorization";
//        $result[] = "Magento_Authorizenet";
//        $result[] = "Magento_Backend";
//        $result[] = "Magento_Backup";
//        $result[] = "Magento_Braintree";
//        $result[] = "Magento_Bundle";
//        $result[] = "Magento_BundleImportExport";
//        $result[] = "Magento_CacheInvalidate";
//        $result[] = "Magento_Captcha";
//        $result[] = "Magento_Catalog";
//        $result[] = "Magento_CatalogImportExport";
//        $result[] = "Magento_CatalogInventory";
//        $result[] = "Magento_CatalogRule";
//        $result[] = "Magento_CatalogRuleConfigurable";
//        $result[] = "Magento_CatalogSearch";
//        $result[] = "Magento_CatalogUrlRewrite";
//        $result[] = "Magento_CatalogWidget";
//        $result[] = "Magento_Checkout";
//        $result[] = "Magento_CheckoutAgreements";
//        $result[] = "Magento_Cms";
//        $result[] = "Magento_CmsUrlRewrite";
//        $result[] = "Magento_Config";
//        $result[] = "Magento_ConfigurableImportExport";
//        $result[] = "Magento_ConfigurableProduct";
//        $result[] = "Magento_Contact";
//        $result[] = "Magento_Cookie";
//        $result[] = "Magento_Cron";
//        $result[] = "Magento_CurrencySymbol";
//        $result[] = "Magento_Customer";
//        $result[] = "Magento_CustomerImportExport";
//        $result[] = "Magento_Deploy";
//        $result[] = "Magento_Developer";
//        $result[] = "Magento_Dhl";
//        $result[] = "Magento_Directory";
//        $result[] = "Magento_Downloadable";
//        $result[] = "Magento_DownloadableImportExport";
//        $result[] = "Magento_Eav";
//        $result[] = "Magento_Email";
//        $result[] = "Magento_EncryptionKey";
//        $result[] = "Magento_Fedex";
//        $result[] = "Magento_GiftMessage";
//        $result[] = "Magento_GoogleAdwords";
//        $result[] = "Magento_GoogleAnalytics";
//        $result[] = "Magento_GoogleOptimizer";
//        $result[] = "Magento_GroupedImportExport";
//        $result[] = "Magento_GroupedProduct";
//        $result[] = "Magento_ImportExport";
//        $result[] = "Magento_Indexer";
//        $result[] = "Magento_Integration";
//        $result[] = "Magento_LayeredNavigation";
//        $result[] = "Magento_Marketplace";
//        $result[] = "Magento_MediaStorage";
//        $result[] = "Magento_Msrp";
//        $result[] = "Magento_Multishipping";
//        $result[] = "Magento_NewRelicReporting";
//        $result[] = "Magento_Newsletter";
//        $result[] = "Magento_OfflinePayments";
//        $result[] = "Magento_OfflineShipping";
        $result[] = "Magento_PageCache";
        $result[] = "Magento_Payment";
        $result[] = "Magento_Paypal";
        $result[] = "Magento_Persistent";
        $result[] = "Magento_ProductAlert";
        $result[] = "Magento_ProductVideo";
        $result[] = "Magento_Quote";
        $result[] = "Magento_Reports";
        $result[] = "Magento_RequireJs";
        $result[] = "Magento_Review";
        $result[] = "Magento_Rss";
        $result[] = "Magento_Rule";
        $result[] = "Magento_Sales";
        $result[] = "Magento_SalesRule";
        $result[] = "Magento_SalesSequence";
        $result[] = "Magento_SampleData";
        $result[] = "Magento_Search";
        $result[] = "Magento_SendFriend";
        $result[] = "Magento_Shipping";
        $result[] = "Magento_Sitemap";
        $result[] = "Magento_Store";
        $result[] = "Magento_Swagger";
        $result[] = "Magento_Swatches";
        $result[] = "Magento_Tax";
        $result[] = "Magento_TaxImportExport";
        $result[] = "Magento_TestModule1";
        $result[] = "Magento_TestModule2";
        $result[] = "Magento_TestModule3";
        $result[] = "Magento_TestModule4";
        $result[] = "Magento_TestModule5";
        $result[] = "Magento_TestModuleIntegrationFromConfig";
        $result[] = "Magento_TestModuleJoinDirectives";
        $result[] = "Magento_TestModuleMSC";
        $result[] = "Magento_Theme";
        $result[] = "Magento_Translation";
        $result[] = "Magento_Ui";
        $result[] = "Magento_Ups";
        $result[] = "Magento_UrlRewrite";
        $result[] = "Magento_User";
        $result[] = "Magento_Usps";
        $result[] = "Magento_Variable";
        $result[] = "Magento_Version";
        $result[] = "Magento_Webapi";
        $result[] = "Magento_WebapiSecurity";
        $result[] = "Magento_Weee";
        $result[] = "Magento_Widget";
        $result[] = "Magento_Wishlist";
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
