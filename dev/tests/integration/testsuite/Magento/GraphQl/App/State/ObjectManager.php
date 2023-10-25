<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App\State;

use Magento\Framework\Event\ObserverFactory;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\TestFramework\ObjectManager as TestFrameworkObjectManager;
use Weakmap;

/**
 * ObjectManager decorator used by GraphQlStateTest for resetting objects and getting initial properties from objects
 */
class ObjectManager extends TestFrameworkObjectManager
{
    /**
     * Constructs this instance by copying test framework's ObjectManager
     *
     * @param TestFrameworkObjectManager $testFrameworkObjectManager
     */
    private $bootstrappedObjects = [
        'Magento\Framework\App\Filesystem\DirectoryList',
        'Magento\Framework\App\DeploymentConfig',
        'Magento\Framework\Filesystem\DirectoryList',
        'Magento\Framework\Filesystem\DriverPool',
        'Magento\Framework\ObjectManager\RelationsInterface',
        'Magento\Framework\Interception\DefinitionInterface',
        'Magento\Framework\ObjectManager\ConfigInterface',
        'Magento\Framework\Interception\ObjectManager\ConfigInterface',
        'Magento\Framework\ObjectManager\DefinitionInterface',
        'Magento\Framework\Stdlib\BooleanUtils',
        'Magento\Framework\ObjectManager\Config\Mapper\Dom',
        'Magento\Framework\ObjectManager\ConfigLoaderInterface',
        'Magento\TestFramework\ObjectManager\Config',
        'Magento\Framework\ObjectManagerInterface',
        'Magento\RemoteStorage\Model\Config',
        'Magento\RemoteStorage\Driver\Adapter\MetadataProviderInterfaceFactory',
        'Magento\RemoteStorage\Driver\Adapter\Cache\CacheInterfaceFactory',
        'Magento\RemoteStorage\Driver\Adapter\CachedAdapterInterfaceFactory',
        'Magento\Framework\App\Cache\Proxy',
        'Aws\Credentials\CredentialsFactory',
        'Magento\Framework\Serialize\Serializer\Json',
        'Magento\AwsS3\Driver\CredentialsCache',
        'Magento\AwsS3\Driver\CachedCredentialsProvider',
        'Magento\AwsS3\Driver\AwsS3Factory',
        'Magento\RemoteStorage\Driver\DriverFactoryPool',
        'Magento\RemoteStorage\Driver\DriverPool',
        'remoteReadFactory',
        'Magento\RemoteStorage\Model\Filesystem\Directory\WriteFactory',
        'customRemoteFilesystem',
        'Magento\Framework\App\ResourceConnection\Proxy',
        'Magento\Framework\App\Cache\Frontend\Factory',
        'Magento\Framework\App\Cache\Frontend\Pool',
        'Magento\Framework\App\Cache\Type\FrontendPool',
        'Magento\Framework\App\Cache\Type\Config',
        'Magento\Framework\ObjectManager\Config\Reader\DomFactory',
        'Magento\Framework\Serialize\Serializer\Serialize',
        'Magento\Framework\App\ObjectManager\ConfigLoader',
        'Magento\Framework\Filesystem\Directory\ReadFactory',
        'Magento\Framework\Filesystem\Directory\WriteFactory',
        'Magento\TestFramework\App\Filesystem',
        'Magento\Framework\Filesystem',
        'Magento\Framework\Filesystem\Driver\File',
        'Magento\Framework\App\AreaList\Proxy',
        'Magento\Framework\Config\Scope',
        'Magento\Framework\ObjectManager\Config\Reader\Dom\Proxy',
        'Psr\Log\LoggerInterface\Proxy',
        'Magento\Framework\Interception\PluginListGenerator',
        'Magento\TestFramework\Interception\PluginList',
        'Magento\Framework\App\State',
        'Magento\Framework\Logger\LoggerProxy',
        'Magento\TestFramework\ErrorLog\Logger',
        'Magento\Framework\App\ResourceConnection\Config\Reader\Proxy',
        'Magento\Framework\App\Cache\Type\Config\Proxy',
        'Magento\Framework\App\ResourceConnection\Config',
        'Magento\Framework\App\ResourceConnection\ConnectionFactory',
        'Magento\Framework\Config\File\ConfigFilePool',
        'Magento\Framework\App\DeploymentConfig\Reader',
        'Magento\Framework\Stdlib\Cookie\PhpCookieReader',
        'Magento\Framework\Stdlib\StringUtils',
        'Magento\Framework\App\Route\ConfigInterface\Proxy',
        'Magento\Backend\App\Request\PathInfoProcessor\Proxy',
        'Magento\Framework\App\Request\PathInfo',
        'Magento\TestFramework\Request',
        'Magento\ResourceConnections\App\DeploymentConfig',
        'Magento\Framework\App\ResourceConnection',
        'Magento\Framework\Model\ResourceModel\Db\TransactionManager',
        'Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor',
        'Magento\Framework\Model\ResourceModel\Db\Context',
        'Magento\SalesSequence\Model\MetaFactory',
        'Magento\SalesSequence\Model\ProfileFactory',
        'Magento\SalesSequence\Model\ResourceModel\Profile',
        'Magento\SalesSequence\Model\ResourceModel\Meta',
        'Magento\Framework\DB\Ddl\Sequence',
        'Magento\TestFramework\Db\Sequence\Builder',
        'Magento\SalesSequence\Model\Builder',
        'Magento\Framework\Event\Config\Reader\Proxy',
        'Magento\Framework\Event\Config\Data',
        'Magento\Framework\App\Request\Http\Proxy',
        'Magento\Framework\Cache\InvalidateLogger',
        'Magento\Framework\App\DeploymentConfig\Writer',
        'Magento\Framework\App\Cache\State',
        'Magento\Framework\App\Area\FrontNameResolverFactory',
//        'Magento\Framework\App\AreaList',
        'Magento\Staging\Model\Event\Manager\Proxy',
        'Magento\Theme\Model\View\Design\Proxy',
        'Magento\Framework\App\Cache\Type\Translate',
        'Magento\Framework\View\Design\Fallback\Rule\SimpleFactory',
        'Magento\Framework\View\Design\Fallback\Rule\ThemeFactory',
        'Magento\Framework\View\Design\Fallback\Rule\ModuleFactory',
        'Magento\Framework\View\Design\Fallback\Rule\ModularSwitchFactory',
        'Magento\Framework\View\Design\Fallback\RulePool',
        'Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple',
        'Magento\Framework\View\Design\FileResolution\Fallback\File',
        'Magento\Framework\View\Template\Html\Minifier',
        'Magento\TestFramework\App\State',
        'Magento\Framework\App\ScopeResolver',
        'Magento\Store\Model\StoreManagerInterface\Proxy',
        'Magento\Store\Model\Resolver\Store',
        'Magento\Store\Model\Resolver\Group',
        'Magento\Store\Model\Resolver\Website',
        'Magento\Framework\App\ScopeResolverPool',
        'Magento\Framework\App\Config\ScopeCodeResolver',
        'scopesConfigSourceAggregatedProxy',
        'Magento\Store\App\Config\Type\Scopes',
        'systemConfigSourceAggregatedProxy',
        'systemConfigPostProcessorCompositeProxy',
        'Magento\Store\Model\ResourceModel\Store',
        'Magento\Store\Model\ResourceModel\Website',
        'Magento\Store\Model\Config\Processor\Fallback',
        'Magento\Theme\Model\Config\Processor\DesignTheme\Proxy',
        'Magento\Config\Model\Placeholder\PlaceholderFactory',
        'Magento\Framework\Stdlib\ArrayManager',
        'Magento\Config\Model\Config\Processor\EnvironmentPlaceholder',
        'Magento\Framework\App\Config\PreProcessorComposite',
        'Magento\Config\App\Config\Type\System\Reader\Proxy',
        'Magento\Framework\Lock\LockBackendFactory',
        'Magento\Framework\Lock\Proxy',
        'systemConfigQueryLocker',
        'Magento\Framework\Math\Random',
        'Magento\Framework\Encryption\KeyValidator',
        'Magento\Framework\Encryption\Encryptor',
        'Magento\Config\App\Config\Type\System',
        'Magento\Translation\Model\Source\InitialTranslationSource\Proxy',
        'translationConfigInitialDataProvider',
        'translationConfigSourceAggregated',
        'Magento\Translation\App\Config\Type\Translation',
        'Magento\TestFramework\App\Config',
        'Magento\Framework\View\Asset\Config',
        'Magento\Framework\View\Design\FileResolution\Fallback\TemplateFile',
        'Magento\Framework\View\Design\FileResolution\Fallback\LocaleFile',
        'viewFileFallbackResolver',
        'Magento\Framework\View\Asset\Minification',
        'viewFileMinifiedFallbackResolver',
        'Magento\Framework\View\Design\FileResolution\Fallback\StaticFile',
        'Magento\Framework\View\Design\FileResolution\Fallback\EmailTemplateFile',
        'Magento\Framework\App\Route\Config\Reader\Proxy',
        'Magento\Framework\App\Route\Config',
        'Magento\Framework\Url\SecurityInfo\Proxy',
        'Magento\Framework\Url\ScopeResolver',
        'Magento\Framework\Session\Generic\Proxy',
        'Magento\Framework\Session\SidResolver\Proxy',
        'Magento\Framework\Url\RouteParamsResolverFactory',
        'Magento\Framework\Url\QueryParamsResolver',
        'Magento\Staging\Model\VersionManager\Proxy',
        'Magento\Staging\Model\Preview\RequestSigner\Proxy',
        'Magento\Staging\Model\Preview\RouteParamsPreprocessor',
        'Magento\Framework\Url\RouteParamsPreprocessorComposite',
        'Magento\Framework\Url\HostChecker',
        'Magento\Framework\Url',
        'Magento\Framework\Data\Collection\EntityFactory',
        'Magento\Framework\Config\ThemeFactory',
        'Magento\Framework\Component\ComponentRegistrar',
        'Magento\Framework\View\Design\Theme\ThemePackageFactory',
        'Magento\Framework\View\Design\Theme\ThemePackageList',
        'Magento\Theme\Model\Theme\Collection',
        'Magento\Framework\View\Asset\PreProcessor\Helper\Sort',
        'AssetPreProcessorPool',
        'Magento\Framework\View\Asset\PreProcessor\ChainFactory',
        'Magento\Framework\View\Asset\Source',
        'Magento\Framework\View\Asset\FileFactory',
        'Magento\Framework\View\Asset\File\FallbackContextFactory',
        'Magento\Framework\View\Asset\File\ContextFactory',
        'Magento\Framework\View\Asset\RemoteFactory',
        'Magento\Framework\View\Asset\Repository',
        'Magento\Framework\View\FileSystem',
        'Magento\Framework\Module\Declaration\Converter\Dom',
        'Magento\Framework\Module\ModuleList\Loader',
        'Magento\Framework\Module\ModuleList',
        'Magento\Framework\Module\Dir',
        'Magento\Framework\Filesystem\File\ReadFactory',
        'Magento\Framework\Config\FileIteratorFactory',
        'Magento\Framework\Module\Dir\Reader',
        'Magento\Framework\Translate\ResourceInterface\Proxy',
        'Magento\Framework\Locale\Resolver\Proxy',
        'Magento\Framework\File\Csv',
        'Magento\Framework\App\Language\ConfigFactory',
        'Magento\Framework\App\Language\ConfigFactory',
        'Magento\Framework\Translate',
        'Magento\Theme\Model\Design\Proxy',
        'Magento\Framework\View\DesignExceptions',
        'Magento\TestFramework\ObjectManager\Configurator',
        'Magento\Framework\Phrase\Renderer\Placeholder',
        'Magento\SalesSequence\Model\EntityPool',
        'Magento\TestFramework\Db\Sequence',
        'Magento\Framework\DB\Adapter\Pdo\MysqlFactory',
        'Magento\Framework\DB\Logger\FileFactory',
        'Magento\Framework\DB\Logger\QuietFactory',
        'Magento\Framework\DB\Logger\LoggerProxy',
        'Magento\Framework\DB\Select\RendererProxy',
        'Magento\Framework\DB\SelectFactory',
        'Magento\Framework\Stdlib\DateTime',
        'Magento\Framework\DB\Adapter\DdlCache',
    ];
    public function __construct(TestFrameworkObjectManager $testFrameworkObjectManager)
    {
        /* Note: PHP doesn't have copy constructors, so we have to use get_object_vars,
         * but luckily all the properties in the superclass are protected. */
        $properties = get_object_vars($testFrameworkObjectManager);
        foreach ($properties as $key => $value) {
            if ($key=== '_sharedInstances') {
                foreach($value as $class => $instance) {
                    if (in_array($class, $this->bootstrappedObjects)) {
                        $this->_sharedInstances[$class] = $instance;
                    }
                }
            } else {
                $this->$key = $value;
            }

        }
//        unset($this->_sharedInstances['Magento\Framework\Event\ObserverFactory']);
        $this->_sharedInstances['Magento\Framework\ObjectManagerInterface'] = $this;
        $this->_sharedInstances['Magento\Framework\App\ObjectManager'] = $this;
//        unset($this->_sharedInstances['Magento\Framework\App\AreaList']);
//        unset($testFrameworkObjectManager->_sharedInstances['Magento\Framework\App\AreaList']);
        unset($this->_sharedInstances['Magento\Staging\Model\Event\Manager\Proxy']);
        unset($this->_sharedInstances['Magento\Framework\Interception\PluginListInterface']);
        unset($this->_sharedInstances['Magento\TestFramework\Interception\PluginList']);
        $this->_factory = new DynamicFactoryDecorator($this->_factory, $this);
    }

    /**
     * Returns the WeakMap used by DynamicFactoryDecorator
     *
     * @return WeakMap
     */
    public function getWeakMap() : WeakMap
    {
        return $this->_factory->getWeakMap();
    }

    /**
     * Returns shared instances
     *
     * @return object[]
     */
    public function getSharedInstances() : array
    {
        return $this->_sharedInstances;
    }

    /**
     * Resets all factory objects that implement ResetAfterRequestInterface
     */
    public function resetStateWeakMapObjects() : void
    {
        $this->_factory->_resetState();
    }

    /**
     * Resets all objects sharing state & implementing ResetAfterRequestInterface
     */
    public function resetStateSharedInstances() : void
    {
        /** @var object $object */
        foreach ($this->_sharedInstances as $object) {
            if ($object instanceof ResetAfterRequestInterface) {
                $object->_resetState();
            }
        }
    }
}
