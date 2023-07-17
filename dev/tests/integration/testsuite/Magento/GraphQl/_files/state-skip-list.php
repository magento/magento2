<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/* These classes are skipped completely during comparison. */
return [
    'navigationMenu' => [
        Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ExtractDataFromCategoryTree::class => null,
        Magento\Customer\Model\Session::class => null,
        Magento\Framework\GraphQl\Query\Fields::class => null,
        Magento\Framework\Session\Generic::class => null,
    ],
    'productDetailByName' => [
        Magento\Customer\Model\Session::class => null,
        Magento\Framework\GraphQl\Query\Fields::class => null,
        Magento\Framework\Session\Generic::class => null,
        Magento\Store\Model\GroupRepository::class => null,
    ],
    'category' => [
        Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ExtractDataFromCategoryTree::class => null,
        Magento\Framework\GraphQl\Query\Fields::class => null,
    ],
    'productDetail' => [
        Magento\Framework\GraphQl\Query\Fields::class => null,
    ],
    'resolveUrl' => [
        Magento\Framework\GraphQl\Query\Fields::class => null,
    ],
    '*' => [
        Magento\TestFramework\Interception\PluginList::class => null,
        // memory leak, wrong sql, potential issues
        Magento\Framework\Event\Config\Data::class => null,
        Magento\Framework\App\AreaList::class => null,
        'customRemoteFilesystem' => null,
        Magento\Store\App\Config\Type\Scopes::class => null,
        Magento\Framework\Module\Dir\Reader::class => null,
        Magento\Framework\Module\PackageInfo::class => null,
        Magento\Framework\App\Language\Dictionary::class => null,
        Magento\Framework\ObjectManager\ConfigInterface::class => null,
        Magento\Framework\App\Cache\Type\Config::class => null,
        Magento\Framework\Interception\PluginListGenerator::class => null,
        Magento\TestFramework\App\Config::class => null,
        Magento\TestFramework\Request::class => null,
        Magento\Framework\View\FileSystem::class => null,
        Magento\Framework\App\Config\FileResolver::class => null,
        Magento\TestFramework\ErrorLog\Logger::class => null,
        'translationConfigSourceAggregated' => null,
        Magento\Framework\App\Request\Http\Proxy::class => null,
        Magento\Framework\Event\Config\Reader\Proxy::class => null,
        Magento\Theme\Model\View\Design\Proxy::class => null,
        Magento\Translation\Model\Source\InitialTranslationSource\Proxy::class => null,
        Magento\Translation\App\Config\Type\Translation::class => null,
        Magento\Backend\App\Request\PathInfoProcessor\Proxy::class => null,
        Magento\Framework\View\Asset\Source::class => null,
        Magento\Framework\Translate\ResourceInterface\Proxy::class => null,
        Magento\Framework\Locale\Resolver\Proxy::class => null,
        Magento\MediaStorage\Helper\File\Storage\Database::class => null,
        Magento\Framework\App\Cache\Proxy::class => null,
        Magento\Framework\Translate::class => null,
        Magento\Store\Model\StoreManager::class => null,
        Magento\Framework\App\Http\Context::class => null,
        Magento\TestFramework\Response::class => null,
        Magento\Store\Model\WebsiteRepository::class => null,
        Magento\Framework\Locale\Resolver::class => null,
        Magento\Store\Model\GroupRepository::class => null,
        Magento\Store\Model\StoreRepository::class => null,
        Magento\Framework\View\Design\Fallback\RulePool::class => null,
        Magento\Framework\View\Asset\Repository::class => null,
        Magento\Framework\HTTP\Header::class => null,
        Magento\Framework\App\Route\Config::class => null,
        Magento\Store\Model\System\Store::class => null,
        Magento\AwsS3\Driver\CredentialsCache::class => null,
        Magento\Eav\Model\Config::class => null,
        'AssetPreProcessorPool' => null,
        Magento\GraphQl\Model\Query\ContextFactory::class => null,
        'viewFileMinifiedFallbackResolver' => null,
        Magento\Framework\View\Asset\Minification::class => null,
        Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection::class => null,
        Magento\Framework\Url::class => null,
        Magento\Framework\HTTP\PhpEnvironment\RemoteAddress::class => null,
    ],
    '' => [
    ],
];
