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
        Magento\Framework\Module\ModuleList::class => null,
        Magento\Framework\Module\Manager::class => null,
        /* AddUserInfoToContext has userContext changed by Magento\GraphQl\Model\Query\ContextFactory,
         * but we need to make this more robust in secure in case of unforeseen bugs.
         * resetState for userContext makes sense, but we need to make sure that it cannot copy current userContext. */
        Magento\CustomerGraphQl\Model\Context\AddUserInfoToContext::class => null, // FIXME: see above comment
        Magento\Framework\ObjectManager\DefinitionInterface::class => null,
        Magento\TestFramework\App\State::class => null,
        Magento\GraphQl\App\State\SkipListAndFilterList::class => null, // Yes, our test uses mutable state itself :-)
    ],
    '*-fromConstructed' => [
        Magento\GraphQl\App\State\ObjectManager::class => null,
        Magento\RemoteStorage\Filesystem::class => null,
        Magento\Framework\App\Cache\Frontend\Factory::class => null,
        Magento\Framework\Config\Scope::class => null,
        Magento\TestFramework\ObjectManager\Config::class => null,
        Magento\Framework\ObjectManager\Definition\Runtime::class => null,
        Magento\Framework\Cache\LockGuardedCacheLoader::class => null,
        Magento\Config\App\Config\Type\System::class => null,
        Magento\Framework\View\Asset\PreProcessor\Pool::class => null,
        Magento\Framework\Xml\Parser::class => null, # TODO: why?!?! errorHandlerIsActive
        Magento\Framework\App\Area::class => null,
        Magento\Store\Model\Store\Interceptor::class => null,
        Magento\GraphQl\App\State\Comparator::class => null, // Yes, our test uses mutable state itself :-)
        Magento\Framework\GraphQl\Query\QueryParser::class => null, // TODO: Do we need to add a reset for when config changes?
        Magento\Framework\App\Http\Context\Interceptor::class => null,
        Magento\Framework\HTTP\LaminasClient::class => null,
        Magento\Customer\Model\GroupRegistry::class => null, // FIXME: This looks like it needs _resetState or else it would be bug
        Magento\Framework\Model\ResourceModel\Db\VersionControl\Metadata::class => null,
        Magento\Framework\App\DeploymentConfig::class => null,
        Laminas\Uri\Uri::class => null,
        Magento\Framework\App\Cache\Frontend\Pool::class => null,
        Magento\Framework\App\Cache\State::class => null, // TODO: Need to confirm that this gets reset when poison pill triggers
        Magento\TestFramework\App\State\Interceptor::class => null,
        Magento\TestFramework\App\MutableScopeConfig::class => null,
        Magento\TestFramework\Store\StoreManager::class => null,
        Magento\TestFramework\Workaround\Override\Config\RelationsCollector::class => null,
        Magento\Framework\Translate\Inline::class => null, // TODO: Need to confirm that this gets reset when poison pill triggers
        Magento\Framework\Reflection\MethodsMap::class => null,
        Magento\Framework\Session\SaveHandler::class => null,
        Magento\Customer\Model\GroupRegistry::class => null, // FIXME: Needs _resetState for $registry
        Magento\Customer\Model\Group\Interceptor::class => null,
        Magento\Store\Model\Group\Interceptor::class => null,
        Magento\Directory\Model\Currency\Interceptor::class => null,
        Magento\Theme\Model\Theme\ThemeProvider::class => null, // Needs _resetState for themes
        Magento\Theme\Model\View\Design::class => null,
        Magento\Catalog\Model\Category\AttributeRepository::class => null, // FIXME: Needs resetState OR reset when poison pill triggered.
        Magento\Framework\Search\Request\Cleaner::class => null,  // FIXME: Needs resetState
        Magento\Catalog\Model\ResourceModel\Category\Interceptor::class => null,
        Magento\Catalog\Model\Attribute\Backend\DefaultBackend\Interceptor::class => null,
        Magento\GraphQlCache\Model\Resolver\IdentityPool::class => null,
        Magento\Inventory\Model\Stock::class => null,
        Magento\InventorySales\Model\SalesChannel::class => null,
        Magento\InventoryApi\Api\Data\StockExtension::class => null,
        Magento\Elasticsearch\Model\Adapter\FieldMapper\FieldMapperResolver::class => null,
        Magento\Catalog\Model\ResourceModel\Eav\Attribute\Interceptor::class => null,
        Magento\Catalog\Model\Category\Attribute\Backend\Image\Interceptor::class => null,
        Magento\Catalog\Model\Attribute\Backend\Startdate\Interceptor::class => null,
        Magento\Eav\Model\Entity\Attribute\Backend\Datetime\Interceptor::class => null,
        Magento\Catalog\Model\Category\Attribute\Backend\Sortby\Interceptor::class => null,
        Magento\Catalog\Model\Category\Attribute\Backend\LayoutUpdate\Interceptor::class => null,
        Magento\Catalog\Model\Attribute\Backend\Customlayoutupdate\Interceptor::class => null,
        Magento\Eav\Model\Entity\Attribute\Backend\Time\Created\Interceptor::class => null,
        Magento\Eav\Model\Entity\AttributeBackendTime\Updated\Interceptor::class => null,
        Magento\Eav\Model\Entity\Attribute\Backend\Increment\Interceptor::class => null,
        Magento\Eav\Model\Entity\Interceptor::class => null,
        Magento\Framework\View\Asset\RepositoryMap::class => null, // TODO: does this need to reset on poison pill trigger?
        Magento\Framework\Url\RouteParamsResolver\Interceptor::class => null,
        Magento\Theme\Model\Theme::class => null,
        Magento\Catalog\Model\ResourceModel\Category\Collection\Interceptor::class => null,
        Magento\Catalog\Model\Category\Interceptor::class => null,
        Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree\Wrapper\NodeWrapper::class => null,
        Magento\Framework\Api\AttributeValue::class => null,
        Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation::class => null,
        Magento\Catalog\Model\ResourceModel\Product\Interceptor::class => null,
        Magento\Catalog\Model\ResourceModel\Product\Collection\Interceptor::class => null,
        Magento\Framework\Api\Search\SearchCriteria::class => null,
        Magento\Framework\Api\SortOrder::class => null,
        Magento\Framework\Api\Search\SearchResult::class => null,
        Magento\Eav\Model\Entity\Attribute\Backend\Time\Updated\Interceptor::class => null,
        Magento\CatalogInventory\Model\Stock\Item\Interceptor::class => null,
        Magento\Framework\View\Asset\File::class => null,
        Magento\Customer\Model\Attribute\Interceptor::class => null,
        Magento\Framework\GraphQl\Schema\SchemaGenerator::class => null,
        Magento\Customer\Model\ResourceModel\Customer::class => null,
        Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class => null,
        Magento\Framework\App\PageCache\Version::class => null,
        Magento\Framework\App\PageCache\Identifier::class => null,
        Magento\Framework\App\PageCache\Kernel::class => null,
        Magento\Translation\Model\Source\InitialTranslationSource::class => null,
        Magento\Framework\GraphQl\Schema\Type\Output\OutputMapper::class => null,
        Magento\Framework\GraphQl\Schema\Type\Input\InputMapper::class => null,
        Magento\Framework\Filesystem\DriverPool::class => null,
        Magento\Framework\Filesystem\Directory\WriteFactory::class => null,
        Magento\Catalog\Model\Product\Media\Config::class => null,
        Magento\Catalog\Model\Product\Type\Interceptor::class => null, // Note: We may need to check to see if this needs to be reset when config changes
        Magento\ConfigurableProduct\Model\Product\Type\Configurable\Interceptor::class => null,
        Magento\Catalog\Model\Product\Type\Simple\Interceptor::class => null,
        Magento\Customer\Model\Session\Storage::class => null,  // FIXME: race condition with Magento\Customer\Model\Session::_resetState()
        Magento\Framework\Module\Manager::class => null,
        Magento\Eav\Api\Data\AttributeExtension::class => null, // FIXME: This needs to be fixed.   is_pagebuilder_enabled 0 => null
        Magento\TestFramework\Event\Magento::class => null,
    ],
    '' => [
    ],
];
