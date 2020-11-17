<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Widget\Link as ProductLink;
use Magento\Catalog\Block\Widget\RecentlyCompared;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Block\Widget\Page\Link as PageLink;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Theme\Model\ResourceModel\Theme as ThemeResource;
use Magento\Theme\Model\ThemeFactory;
use Magento\Widget\Model\ResourceModel\Widget\Instance as InstanceResource;
use Magento\Widget\Model\Widget\InstanceFactory;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/second_product_simple.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ThemeFactory $themeFactory */
$themeFactory = $objectManager->get(ThemeFactory::class);
/** @var ThemeResource $themeResource */
$themeResource = $objectManager->get(ThemeResource::class);
$lumaTheme = $themeFactory->create();
$themeResource->load($lumaTheme, 'Magento/luma', 'code');
$blankTheme = $themeFactory->create();
$themeResource->load($blankTheme, 'Magento/blank', 'code');
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$defaultStoreId = (int)$storeManager->getStore('default')->getId();
/** @var GetPageByIdentifierInterface $getPageByIdentifier */
$getPageByIdentifier = $objectManager->get(GetPageByIdentifierInterface::class);
$homePage = $getPageByIdentifier->execute('home', $defaultStoreId);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();
$productId = (int)$productRepository->get('simple2')->getId();
/** @var InstanceFactory $widgetFactory */
$widgetFactory = $objectManager->get(InstanceFactory::class);
/** @var InstanceResource $widgetResource */
$widgetResource = $objectManager->get(InstanceResource::class);
$cmsPageWidget = $widgetFactory->create();
$cmsPageWidgetData = [
    'instance_type' => PageLink::class,
    'instance_code' => 'cms_page_link',
    'theme_id' => $lumaTheme->getId(),
    'title' => 'cms page widget title',
    'sort_order' => 3,
    'store_ids' => [$defaultStoreId],
    'widget_parameters' => [
        'page_id' => $homePage->getId(),
    ],
];
$cmsPageWidget->setData($cmsPageWidgetData);
$widgetResource->save($cmsPageWidget);

$productLinkWidget = $widgetFactory->create();
$productLinkWidgetData = [
    'instance_type' => ProductLink::class,
    'instance_code' => 'catalog_product_link',
    'theme_id' => $lumaTheme->getId(),
    'title' => 'product link widget title',
    'sort_order' => 2,
    'store_ids' => [$defaultStoreId],
    'pages_groups' => [
        'page_group' => 'all_pages',
        'all_pages' => [
            'page_id' => 0,
            'layout_handle' => 'default',
            'for' => 'all',
            'block' => 'content',
            'template' => 'product/widget/link/link_block.phtml',
        ],
    ],
    'widget_parameters' => [
        'product/' . $productId,
    ],
];

$productLinkWidget->setData($productLinkWidgetData);
$widgetResource->save($productLinkWidget);

$recentlyComparedProductWidget = $widgetFactory->create();
$recentlyComparedProductWidgetData = [
    'instance_type' => RecentlyCompared::class,
    'instance_code' => 'catalog_recently_compared',
    'theme_id' => $blankTheme->getId(),
    'title' => 'recently compared products',
    'store_ids' => [$defaultStoreId],
    'sort_order' => 1,
    'widget_parameters' => [
        'uiComponent' => 'widget_recently_compared',
        'page_size' => 5,
        'show_attributes' => ['name'],
        'show_buttons' => ['add_to_cart'],
    ],
];
$recentlyComparedProductWidget->setData($recentlyComparedProductWidgetData);
$widgetResource->save($recentlyComparedProductWidget);
