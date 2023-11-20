<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'all' => [ // Note: These will be applied to all services
        '_objectManager' => null,
        'objectManager' => null,
        '_httpRequest' => null, // TODO ? I think this one is okay
        'pluginList' => null, // Interceptors can change their pluginList??
        '_classReader' => null,
        '_eavConfig' => null,
        'eavConfig' => null,
        '_eavEntityType' => null,
        '_moduleReader' => null,
        'attributeLoader' => null,
        'storeRepository' => null,
        'localeResolver' => null,
        '_localeResolver' => null,
    ],
    'parents' => [ // Note: these are parent classes and will match their children as well.
        Magento\Framework\DataObject::class => ['_underscoreCache' => null],
        Magento\Eav\Model\Entity\AbstractEntity::class => [
            '_attributesByTable' => null,
            '_attributesByCode' => null,
            '_staticAttributes' => null,
        ],
        Magento\Framework\Model\ResourceModel\Db\AbstractDb::class => ['_tables' => null],
        Magento\Framework\App\ResourceConnection::class => [
            'config' => null, // $_connectionNames changes
            'connections' => null,
        ],
        /* All Proxy classes use NoninterceptableInterface.  We filter _subject on them because for the Proxies that
         * are loaded, we compare the actual loaded objects. */
        Magento\Framework\ObjectManager\NoninterceptableInterface::class => [
            '_subject' => null,
        ],
        Magento\Framework\Logger\Handler\Base::class => [ // TODO: remove this after ACPT-1034 is fixed
            'stream' => null,
        ],
    ],
    'services' => [ // Note: These apply only to the service names that match.
        Magento\Framework\ObjectManager\ConfigInterface::class => ['_mergedArguments' => null],
        Magento\Framework\ObjectManager\DefinitionInterface::class => ['_definitions' => null],
        Magento\Framework\App\Cache\Type\FrontendPool::class => ['_instances' => null],
        Magento\Framework\GraphQl\Schema\Type\TypeRegistry::class => ['types' => null],
        Magento\Framework\Filesystem::class => ['readInstances' => null, 'writeInstances' => null],
        Magento\Framework\EntityManager\TypeResolver::class => [
            'typeMapping' => null
        ],
        Magento\Framework\App\View\Deployment\Version::class => [
            'cachedValue' => null // deployment version of static files
        ],
        Magento\Framework\View\Asset\Minification::class => ['configCache' => null], // TODO: depends on mode
        Magento\Eav\Model\Config::class => [ // TODO: is this risky?
            'attributeProto' => null,
            'attributesPerSet' => null,
            'attributes' => null,
            '_objects' => null,
            '_references' => null,
        ],
        Magento\Framework\Api\ExtensionAttributesFactory::class => ['classInterfaceMap' => null],
        Magento\Catalog\Model\ResourceModel\Category::class => ['_isActiveAttributeId' => null],
        Magento\Eav\Model\ResourceModel\Entity\Type::class => ['additionalAttributeTables' => null],
        Magento\Framework\Reflection\MethodsMap::class => ['serviceInterfaceMethodsMap' => null],
        Magento\Framework\EntityManager\Sequence\SequenceRegistry::class => ['registry' => null],
        Magento\Framework\EntityManager\MetadataPool::class => ['registry' => null],
        Magento\Framework\App\Config\ScopeCodeResolver::class => ['resolvedScopeCodes' => null],
        Magento\Framework\Cache\InvalidateLogger::class => ['request' => null],
        Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple::class => ['rulePool' => null],
        Magento\Framework\View\Template\Html\Minifier::class => ['filesystem' => null],
        Magento\Store\Model\Config\Processor\Fallback::class => ['scopes' => null],
        'viewFileFallbackResolver' => ['rulePool' => null],
        Magento\Framework\View\Asset\Source::class => ['filesystem' => null],
        Magento\Store\Model\StoreResolver::class => ['request' => null],
        Magento\Framework\Url\Decoder::class => ['urlBuilder' => null],
        Magento\Framework\HTTP\PhpEnvironment\RemoteAddress::class => ['request' => null],
        Magento\Framework\App\Helper\Context::class => ['_urlBuilder' => null],
        Magento\MediaStorage\Helper\File\Storage\Database::class => [
            '_filesystem' => null,
            '_request' => null,
            '_urlBuilder' => null,
        ],
        Magento\Framework\Event\Config::class => ['_dataContainer' => null],
        Magento\TestFramework\Store\StoreManager::class => ['decoratedStoreManager' => null],
        Magento\Eav\Model\ResourceModel\Entity\Attribute::class => ['_eavEntityType' => null],
        Magento\Eav\Model\Entity\AttributeLoader::class => ['defaultAttributes' => null, 'config' => null],
        Magento\Framework\Validator\Factory::class => ['moduleReader' => null],
        Magento\PageCache\Model\Config::class => ['reader' => null],
        Magento\Config\Model\Config\Compiler\IncludeElement::class => ['moduleReader' => null],
        Magento\Customer\Model\Customer::class => ['_config' => null],
        Magento\Framework\Model\Context::class => ['_cacheManager' => null, '_appState' => null],
        Magento\Framework\App\Cache\TypeList::class => ['_cache' => null],
        Magento\GraphQlCache\Model\CacheId\CacheIdCalculator::class => ['contextFactory' => null],
        Magento\Store\Model\Config\Placeholder::class => ['request' => null],
        Magento\Framework\Config\Scope::class => ['_areaList' => null],  // These were added because we switched to ...
        Magento\TestFramework\App\State::class => ['_areaCode' => null], //                                         .
        Magento\Framework\Event\Invoker\InvokerDefault::class => ['_appState' => null], //                          .
        Magento\Developer\Model\Logger\Handler\Debug::class => ['state' => null], //                                .
        Magento\Framework\View\Design\FileResolution\Fallback\TemplateFile::class => //                             .
            ['appState' => null], // ... using Magento\Framework\App\Http for the requests
        Magento\Store\App\Config\Source\RuntimeConfigSource::class =>  ['connection' => null],
        Magento\Framework\Mview\View\Changelog::class =>  ['connection' => null],
        Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection::class =>  ['_conn' => null],
        Magento\Framework\App\Cache\Frontend\Factory::class => ['_filesystem' => null],
        Magento\Framework\App\DeploymentConfig\Writer::class => ['filesystem' => null],
        Magento\Search\Model\SearchEngine::class => ['adapter' => null],
        // TODO: Do we need resetState for the connection?
        Magento\Elasticsearch\SearchAdapter\ConnectionManager::class => ['client' => null],
        // TODO: Do we need resetState for the connection?
        Magento\Elasticsearch7\Model\Client\Elasticsearch::class => ['client' => null],
        // TODO: Do we need resetState for the connection?
        Magento\Webapi\Model\Authorization\TokenUserContext::class => ['request' => null],
        Magento\Framework\Json\Helper\Data::class => ['_request' => null],
        Magento\Directory\Helper\Data::class => ['_request' => null],
        Magento\Paypal\Plugin\TransparentSessionChecker::class => ['request' => null],
        Magento\Backend\App\Area\FrontNameResolver::class => ['request' => null],
        Magento\Backend\Helper\Data::class => ['_request' => null],
        Magento\Framework\Url\Helper\Data::class => ['_request' => null],
        Magento\Customer\Helper\View::class => ['_request' => null],
        Magento\GraphQl\Model\Backpressure\BackpressureContextFactory::class => ['request' => null],
        Magento\Search\Helper\Data::class => ['request' => null],
        Magento\Search\Model\QueryFactory::class => ['request' => null],
        Magento\Catalog\Helper\Product\Flat\Indexer::class => ['_request' => null],
        Magento\Catalog\Model\Product\Gallery\ReadHandler\Interceptor::class => ['attribute' => null],
        Magento\Eav\Model\Entity\Attribute\Source\Table::class => ['_attribute' => null],
        Magento\Catalog\Model\Product\Gallery\ReadHandler::class => ['attribute' => null],
        Magento\Framework\Pricing\Adjustment\Pool::class => ['adjustmentInstances' => null],
        // TODO: Check to make sure this doesn't need reset.
        //  It looks okay on quick debug, but after deep debug,
        // we might find something that needs reset. Or
        // we can just reset it to be safe.
        Magento\Framework\Pricing\Adjustment\Collection::class => ['adjustmentInstances' => null],
        // TODO: Check to make sure this doesn't need reset.
        //  It looks okay on quick debug, but after deep debug, we might find something that needs reset.
        //  Or we can just reset it to be safe.
        Magento\Catalog\Model\ResourceModel\Category\Tree::class => ['_conn' => null],
        Magento\UrlRewrite\Model\Storage\DbStorage::class => ['connection' => null],
        Magento\UrlRewrite\Model\Storage\DbStorage\Interceptor::class => ['connection' => null],
        Magento\CatalogUrlRewrite\Model\Storage\DbStorage::class => ['connection' => null],
        Magento\CatalogUrlRewrite\Model\Storage\DbStorage\Interceptor::class => ['connection' => null],
        Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection\Interceptor::class => ['_conn' => null],
        Magento\Catalog\Model\ResourceModel\Product\Collection::class => ['_conn' => null],
        Magento\Catalog\Model\ResourceModel\Category\Collection::class => ['_conn' => null],
        Magento\Catalog\Model\Product\Attribute\Backend\Tierprice\Interceptor::class =>
            ['metadataPool' => null, '_attribute' => null],
        Magento\Framework\View\Design\Fallback\Rule\Theme::class => [
            'directoryList' => null, // FIXME: This should be using a Dependency Injected Proxy instead
        ],
        Magento\Framework\View\Asset\PreProcessor\AlternativeSource::class => [
            'alternativesSorted' => null, // Note: just lazy loaded the sorting of alternatives
        ],
        Magento\Directory\Model\Country::class => [
            '_origData' => null, // TODO: Do these need to be added to resetState?
            'storedData' => null, // Should this class even be reused at all?
            '_data' => null,
        ],
        Magento\Directory\Model\Region::class => [
            '_origData' => null, // TODO: Do these need to be added to resetState?
            'storedData' => null, // Should this class even be reused at all?
            '_data' => null,
        ],
        Magento\Framework\View\Layout\Argument\Parser::class => [
            // FIXME: Needs to convert to proper dependency injection using constructor and factory
            'converter' => null,
        ],
        Magento\Framework\Communication\Config\Reader\XmlReader\Converter::class => [
            // FIXME: Needs to convert to proper dependency injection using constructor and factory
            'configParser' => null,
        ],
        Magento\Webapi\Model\Config::class => [
            'services' => null, // 'services' is lazy-loaded which is okay,
                                //but we need to verify that it is properly reset after poison pill
        ],
        Magento\WebapiAsync\Model\Config::class => [
            'asyncServices' => null, // 'asyncServices' is lazy-loaded which is okay,
                                    // but we need to verify that it is properly reset after poison pill
        ],
        Magento\Framework\MessageQueue\Publisher\Config\PublisherConnection::class => [
            'name' => null, // TODO: Confirm this doesn't change outside of deployment,
                            // TODO: or if it does, that it resets properly from poison pill
            'exchange' => null,
            'isDisabled' => null,
        ],
        Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItem::class => [
            'topic' => null, // TODO: Confirm this doesn't change outside of deployment,
                            // TODO:  or if it does, that it resets properly from poison pill
            'isDisabled' => null,
        ],
        Magento\Framework\View\File\Collector\Decorator\ModuleDependency::class => [
            'orderedModules' => null, // TODO: Confirm this doesn't change outside of deployment
        ],
        Magento\Framework\View\Page\Config::class => [
            'builder' => null, // I think this is okay
        ],
        Magento\TestFramework\View\Layout\Interceptor::class => [
            'builder' => null,
        ],
        Magento\Theme\Model\ResourceModel\Theme\Collection\Interceptor::class => [
            '_itemObjectClass' => null, // FIXME: this looks like it needs to be fixed
        ],
        Magento\Customer\Model\Metadata\AttributeMetadataCache::class => [
            'isAttributeCacheEnabled' => null, // If cache configuration only changes during deployment, this is okay
        ],
        Magento\Eav\Model\ResourceModel\Entity\Attribute\Set::class => [
            'serializer' => null, // Note: Should use DI instead, but this isn't a big deal
        ],
        Magento\Framework\Escaper::class => [
            'escaper' => null, // Note: just lazy loading without a Proxy.  Should use DI instead, but not big deal
        ],
        Magento\Framework\App\State\Interceptor::class => [
            '_areaCode' => null, // Note: _areaCode gets set after construction.
        ],
        Magento\Framework\Cache\Frontend\Adapter\Zend::class => [
            'parentFrontends' => null, // Note: This is to prevent closing parent thread's connection.
        ],
        Magento\Framework\Session\SaveHandler\Redis::class => [
            'connection' => null, // Note: This is to prevent closing parent thread's connection.
        ],
    ],
];
