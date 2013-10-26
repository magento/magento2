<?php
/**
 * Obsolete class attributes
 *
 * Format: array(<attribute_name>[, <class_scope> = ''[, <replacement>]])
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    array('_addresses', 'Magento\Customer\Model\Customer'),
    array('_addMinimalPrice', 'Magento\Catalog\Model\Resource\Product\Collection'),
    array('_alias', 'Magento\Core\Block\AbstractBlock'),
    array('_anonSuffix'),
    array('_appMode', 'Magento\Core\Model\ObjectManager\ConfigLoader'),
    array('_baseDirCache', 'Magento\Core\Model\Config'),
    array('_cacheConf'),
    array('_canUseLocalModules'),
    array('_checkedProductsQty', 'Magento\CatalogInventory\Model\Observer'),
    array('_children', 'Magento\Core\Block\AbstractBlock'),
    array('_childrenHtmlCache', 'Magento\Core\Block\AbstractBlock'),
    array('_childGroups', 'Magento\Core\Block\AbstractBlock'),
    array('_combineHistory'),
    array('_config', 'Magento\Core\Model\Design\Package'),
    array('_config', 'Magento\Core\Model\Logger', '_dirs'),
    array('_config', 'Magento\Core\Model\Resource\Setup'),
    array('_configModel', 'Magento\Backend\Model\Menu\AbstractDirector'),
    array('_configuration', 'Magento\Index\Model\Lock\Storage', '_dirs'),
    array('_connectionConfig', 'Magento\Core\Model\Resource\Setup'),
    array('_connectionTypes', 'Magento\Core\Model\Resource'),
    array('_currency', 'Magento\GoogleCheckout\Model\Api\Xml\Checkout'),
    array('_currencyNameTable'),
    array('_customEtcDir', 'Magento\Core\Model\Config'),
    array('_defaultTemplates', 'Magento\Core\Model\Email\Template'),
    array('_designProductSettingsApplied'),
    array('_directOutput', 'Magento\Core\Model\Layout'),
    array('_dirs', 'Magento\Core\Model\Resource'),
    array('_distroServerVars'),
    array('_entityIdsToIncrementIds'),
    array('entities', 'Magento\Core\Model\Resource'),
    array('_entityTypeIdsToTypes'),
    array('_factory', 'Magento\Backend\Model\Menu\Config'),
    array('_factory', 'Magento\Backend\Model\Menu\AbstractDirector', '_commandFactory'),
    array('_isAnonymous'),
    array('_isFirstTimeProcessRun', 'Magento\SalesRule\Model\Validator'),
    array('_isRuntimeValidated', 'Magento\ObjectManager\Config\Reader\Dom'),
    array('_loadDefault', 'Magento\Core\Model\Resource\Store\Collection'),
    array('_loadDefault', 'Magento\Core\Model\Resource\Store\Group\Collection'),
    array('_loadDefault', 'Magento\Core\Model\Resource\Website\Collection'),
    array('_mapper', 'Magento\ObjectManager\Config\Reader\Dom'),
    array('_menu', 'Magento\Backend\Model\Menu\Builder'),
    array('_modulesReader', 'Magento\Core\Model\ObjectManager\ConfigLoader'),
    array('_moduleReader', 'Magento\Backend\Model\Menu\Config'),
    array('_option', 'Magento\Captcha\Helper\Data', '_dirs'),
    array('_options', 'Magento\Core\Model\Config', 'Magento\App\Dir'),
    array('_optionsMapping', null, '\Magento\App\Dir::getBaseDir($nodeKey)'),
    array('_order', 'Magento\Checkout\Block\Onepage\Success'),
    array('_order_id'),
    array('_parent', 'Magento\Core\Block\AbstractBlock'),
    array('_parentBlock', 'Magento\Core\Block\AbstractBlock'),
    array('_persistentCustomerGroupId'),
    array('_queriesHooked', 'Magento\Core\Model\Resource\Setup'),
    array('_ratingOptionTable', 'Magento\Rating\Model\Resource\Rating\Option\Collection'),
    array('_readerFactory', 'Magento\Core\Model\ObjectManager\ConfigLoader'),
    array('_resourceConfig', 'Magento\Core\Model\Resource\Setup'),
    array('_saveTemplateFlag', 'Magento\Newsletter\Model\Queue'),
    array('_searchTextFields'),
    array('_setAttributes', 'Magento\Catalog\Model\Product\Type\AbstractType'),
    array('_skipFieldsByModel'),
    array('_ship_id'),
    array('_shipTable', 'Magento\Shipping\Model\Resource\Carrier\Tablerate\Collection'),
    array('_showTemplateHints', 'Magento\Core\Block\Template',
        'Magento\Core\Model\TemplateEngine\Plugin\DebugHints'),
    array('_showTemplateHintsBlocks', 'Magento\Core\Block\Template',
        'Magento\Core\Model\TemplateEngine\Plugin\DebugHints'),
    array('_sortedChildren'),
    array('_sortInstructions'),
    array('_storeFilter', 'Magento\Catalog\Model\Product\Type\AbstractType'),
    array('_substServerVars'),
    array('_track_id'),
    array('_varSubFolders', null, 'Magento\App\Dir'),
    array('_viewDir', 'Magento\Core\Block\Template', '_dirs'),
    array('decoratedIsFirst', null, 'getDecoratedIsFirst'),
    array('decoratedIsEven', null, 'getDecoratedIsEven'),
    array('decoratedIsOdd', null, 'getDecoratedIsOdd'),
    array('decoratedIsLast', null, 'getDecoratedIsLast'),
    array('static', 'Magento\Core\Model\Email\Template\Filter'),
    array('_useAnalyticFunction'),
    array('_defaultIndexer', 'Magento\CatalogInventory\Model\Resource\Indexer\Stock'),
    array('_engine', 'Magento\CatalogSearch\Model\Resource\Fulltext'),
    array('_moduleNamespaces', 'Magento\Core\Model\Config'),
    array('_allowedAreas', 'Magento\Core\Model\Config'),
    array('_app', 'Magento\Core\Block\AbstractBlock'),
    array('_app', 'Magento\Core\Block\Template'),
    array('_config', 'Magento\Backend\Helper\Data'),
    array('_defaultAreaFrontName', 'Magento\Backend\Helper\Data'),
    array('_areaFrontName', 'Magento\Backend\Helper\Data'),
    array('_backendFrontName', 'Magento\Backend\Helper\Data'),
    array('_app', 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency'),
);
