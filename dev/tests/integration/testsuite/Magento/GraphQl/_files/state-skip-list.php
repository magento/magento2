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
        Magento\Framework\Webapi\Response::class => null,
        Magento\TestFramework\App\Filesystem::class => null,
        Magento\TestFramework\Interception\PluginList::class => null,
        // memory leak, wrong sql, potential issues
        Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred\Product::class => null,
        Magento\ConfigurableProductGraphQl\Model\Variant\Collection::class => null,
        Magento\ConfigurableProductGraphQl\Model\Options\Collection::class => null,
        Magento\Framework\Url\QueryParamsResolver::class => null,
        Magento\Framework\Event\Config\Data::class => null,  // TODO: Make sure this is reset when config is reset from poison pill
        Magento\Framework\App\AreaList::class => null,
        'customRemoteFilesystem' => null,
        Magento\Store\App\Config\Type\Scopes::class => null,
        Magento\Framework\Module\Dir\Reader::class => null,
        Magento\Framework\App\Language\Dictionary::class => null,
        Magento\Framework\Code\Reader\ClassReader::class => null,
        Magento\Framework\ObjectManager\ConfigInterface::class => null,
        Magento\Framework\App\Cache\Type\Config::class => null,
        Magento\Framework\Interception\PluginListGenerator::class => null,
        Magento\TestFramework\App\Config::class => null,
        Magento\TestFramework\Request::class => null,
        Magento\Framework\View\FileSystem::class => null,
        Magento\Framework\App\Config\FileResolver::class => null,
        Magento\Framework\Module\Manager::class => null,
        Magento\Framework\Logger\LoggerProxy::class => null,
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
        Magento\Framework\Translate::class => null,  // TODO: ?
        Magento\Store\Model\StoreManager::class => null, // TODO: This is reset with poison pill, right?
        Magento\Framework\App\Http\Context::class => null,  // TODO: This should probably be cleaned up, right?!?
        Magento\Customer\Model\Session\Storage::class => null, // TODO: Didn't Aakash or Kasian fix this already?
        Magento\TestFramework\Response::class => null, // TODO: Why is this in the ObjectManager?!?
        Magento\Store\Model\WebsiteRepository::class => null, // TODO: What is going on here?!?
        Magento\Framework\Locale\Resolver::class => null, // TODO: do we need to fix this?
        Magento\Theme\Model\ResourceModel\Theme\Collection::class => null, // TODO
        Magento\Store\Model\GroupRepository::class => null, // TODO: see what this is
        Magento\Store\Model\StoreRepository::class => null, // TODO: Ask Aakash is this is the one that was fixed already with Poison Pill
        Magento\Framework\View\Design\Fallback\RulePool::class => null, // TODO: rules change.  Looks like we need to reset?
        Magento\Framework\View\Asset\Repository::class => null, // TODO: Looks okay, but need to confirm
        Magento\Framework\HTTP\Header::class => null, // TODO: I believe Aakash is currently working on this
        Magento\Framework\App\Route\Config::class => null, // TODO: Make sure this is reset when Poison Pill causes config to reset.
        Magento\Customer\Model\ResourceModel\Attribute::class => null, // TODO
        Magento\Framework\DataObject\Copy\Config\Converter::class => null, // TODO
        Magento\Framework\DataObject\Copy\Config\SchemaLocator::class => null, // TODO
        Magento\Framework\DataObject\Copy\Config\Reader::class => null, // TODO
        Magento\Framework\DataObject\Copy\Config\Data::class => null, // TODO
        Magento\Store\Model\System\Store::class => null, // TODO
        Magento\AwsS3\Driver\CredentialsCache::class => null, // TODO
        Magento\Eav\Model\Config::class => null, // TODO: Does this work properly after config changes?
        'AssetPreProcessorPool' => null, // TODO: see what this is
        Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider::class => null, // FIXME: this is bug or needs to be reset?
        Magento\GraphQl\Model\Query\Context::class => null, // FIXME: I think this one needs to be reset.  Check!
        Magento\GraphQl\Model\Query\ContextFactory::class => null, // FIXME: I think this one needs to be reset.  Check!
        'viewFileMinifiedFallbackResolver' => null, // FIXME: this MUST be removed from list after Magento\Framework\View\Asset\Minification is fixed
        Magento\Framework\View\Asset\Minification::class => null,  // FIXME: $configCache must be reset
        Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection::class => null, // FIXME: We MUST NOT dependency inject collections.  This needs to be fixed inMagento\CatalogGraphQl\Model\Config\SortAttributeReader
        Magento\Framework\Url::class => null, // FIXME: This need reseter!!
        Magento\Quote\Model\Quote\Address\Total\Collector::class => null, // FIXME: has mutable state that needs to be reset.
        Magento\Framework\HTTP\PhpEnvironment\RemoteAddress::class => null, // FIXME: $remoteAddress caching from $request which has mutable state
    ],
    '' => [
    ],
];
