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
        Magento\Framework\App\ResourceConnection::class => [
            'config' => null, // $_connectionNames changes
            'connections' => null,
        ],
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
    ],
];
