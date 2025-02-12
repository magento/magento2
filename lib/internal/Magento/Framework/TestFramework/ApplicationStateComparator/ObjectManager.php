<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\ApplicationStateComparator;

use Magento\Framework\ObjectManager\Resetter\ResetterInterface;
use Magento\TestFramework\ObjectManager as TestFrameworkObjectManager;

/**
 * ObjectManager decorator used by GraphQlStateTest for resetting objects and getting initial properties from objects
 */
class ObjectManager extends TestFrameworkObjectManager implements ObjectManagerInterface
{
    //phpcs:disable Magento2.PHP.LiteralNamespaces
    /**
     * @var array|string[]
     */
    private array $bootstrappedObjects = [
        // Note: These are after $objectManager = $this->_factory->create($overriddenParams);
        'Magento\Framework\App\DeploymentConfig',
        'Magento\Framework\App\Filesystem\DirectoryList',
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
        // Note: These were added by addSharedInstance
        'Magento\Framework\App\Filesystem\DirectoryList',
        'Magento\Framework\Filesystem\DirectoryList',
        'Magento\Framework\Filesystem',
        'Magento\Framework\Logger\LoggerProxy',
        'Magento\TestFramework\ErrorLog\Logger',
        'Magento\SalesSequence\Model\Builder',
        'Magento\Framework\App\Filesystem\DirectoryList',
        'Magento\Framework\Filesystem\DirectoryList',
        'Magento\Framework\App\DeploymentConfig',
        'Magento\Framework\ObjectManager\ConfigLoaderInterface',
        'Magento\Framework\Filesystem',
        'Magento\Framework\Logger\LoggerProxy',
        'Magento\TestFramework\ErrorLog\Logger',
        'Magento\SalesSequence\Model\Builder',
        'Magento\Framework\App\Filesystem\DirectoryList',
        'Magento\Framework\Filesystem\DirectoryList',
        'Magento\Framework\App\DeploymentConfig',
        'Magento\Framework\ObjectManager\ConfigLoaderInterface',
        'Magento\Framework\Filesystem',
        'Magento\Framework\Logger\LoggerProxy',
        'Magento\TestFramework\ErrorLog\Logger',
        'Magento\SalesSequence\Model\Builder',
        'Magento\Framework\TestFramework\ApplicationStateComparator\SkipListAndFilterList',
        'Magento\Framework\TestFramework\ApplicationStateComparator\Collector',
        'Magento\Framework\App\Filesystem\DirectoryList',
        'Magento\Framework\Filesystem\DirectoryList',
        'Magento\Framework\App\DeploymentConfig',
        'Magento\Framework\ObjectManager\ConfigLoaderInterface',
        'Magento\Framework\Filesystem',
        'Magento\Framework\Logger\LoggerProxy',
        'Magento\TestFramework\ErrorLog\Logger',
        'Magento\SalesSequence\Model\Builder',
    ];

    /**
     * Constructs this instance by copying test framework's ObjectManager
     *
     * @param TestFrameworkObjectManager $testFrameworkObjectManager
     */
    public function __construct(TestFrameworkObjectManager $testFrameworkObjectManager)
    {
        /* Note: PHP doesn't have copy constructors, so we have to use get_object_vars,
         * but luckily all the properties in the superclass are protected. */
        $properties = get_object_vars($testFrameworkObjectManager);
        foreach ($properties as $key => $value) {
            if ($key === '_sharedInstances') {
                foreach ($value as $class => $instance) {
                    if (in_array($class, $this->bootstrappedObjects)) {
                        $this->_sharedInstances[$class] = $instance;
                    }
                }
            } else {
                $this->$key = $value;
            }

        }
        $this->_sharedInstances['Magento\Framework\ObjectManagerInterface'] = $this;
        $this->_sharedInstances['Magento\Framework\App\ObjectManager'] = $this;
        $this->_factory = new DynamicFactoryDecorator($this->_factory, $this);
    }

    /**
     * @inheritDoc
     */
    public function getResetter(): ResetterInterface
    {
        return $this->_factory->getResetter();
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
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->_factory->_resetState();
    }
}
