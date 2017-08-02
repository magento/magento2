<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Setup;

/**
 * Constructor modification point for Magento\Framework\Module\Setup.
 *
 * All context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with
 * the classes they were introduced for.
 * @since 2.0.0
 */
class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.0.0
     */
    protected $_resourceModel;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     * @since 2.0.0
     */
    protected $_modulesReader;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     * @since 2.0.0
     */
    protected $_moduleList;

    /**
     * @var \Magento\Framework\Module\ResourceInterface
     * @since 2.0.0
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Module\Setup\MigrationFactory
     * @since 2.0.0
     */
    protected $_migrationFactory;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     * @since 2.0.0
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\Filesystem
     * @since 2.0.0
     */
    protected $filesystem;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\ResourceConnection $appResource
     * @param \Magento\Framework\Module\Dir\Reader $modulesReader
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Module\ResourceInterface $resource
     * @param \Magento\Framework\Module\Setup\MigrationFactory $migrationFactory
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Filesystem $filesystem
     * @since 2.0.0
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\ResourceConnection $appResource,
        \Magento\Framework\Module\Dir\Reader $modulesReader,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Module\ResourceInterface $resource,
        \Magento\Framework\Module\Setup\MigrationFactory $migrationFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_logger = $logger;
        $this->_eventManager = $eventManager;
        $this->_resourceModel = $appResource;
        $this->_modulesReader = $modulesReader;
        $this->_moduleList = $moduleList;
        $this->_resource = $resource;
        $this->_migrationFactory = $migrationFactory;
        $this->_encryptor = $encryptor;
        $this->filesystem = $filesystem;
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * @return \Psr\Log\LoggerInterface $logger
     * @since 2.0.0
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return \Magento\Framework\Module\ModuleListInterface
     * @since 2.0.0
     */
    public function getModuleList()
    {
        return $this->_moduleList;
    }

    /**
     * @return \Magento\Framework\Module\Dir\Reader
     * @since 2.0.0
     */
    public function getModulesReader()
    {
        return $this->_modulesReader;
    }

    /**
     * @return \Magento\Framework\App\ResourceConnection
     * @since 2.0.0
     */
    public function getResourceModel()
    {
        return $this->_resourceModel;
    }

    /**
     * @return \Magento\Framework\Module\Setup\MigrationFactory
     * @since 2.0.0
     */
    public function getMigrationFactory()
    {
        return $this->_migrationFactory;
    }

    /**
     * @return \Magento\Framework\Module\ResourceInterface
     * @since 2.0.0
     */
    public function getResource()
    {
        return $this->_resource;
    }

    /**
     * @return \Magento\Framework\Encryption\EncryptorInterface
     * @since 2.0.0
     */
    public function getEncryptor()
    {
        return $this->_encryptor;
    }

    /**
     * @return \Magento\Framework\Filesystem
     * @since 2.0.0
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }
}
