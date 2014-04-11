<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Module\Setup;

class Context implements \Magento\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\App\Resource
     */
    protected $_resourceModel;

    /**
     * @var \Magento\Module\Dir\Reader
     */
    protected $_modulesReader;

    /**
     * @var \Magento\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var \Magento\Module\ResourceInterface
     */
    protected $_resourceResource;

    /**
     * @var \Magento\Module\Setup\MigrationFactory
     */
    protected $_migrationFactory;

    /**
     * @var \Magento\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\App\Filesystem
     */
    protected $filesystem;

    /**
     * Constructor
     *
     * @param \Magento\Logger $logger
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\App\Resource $resource
     * @param \Magento\Module\Dir\Reader $modulesReader
     * @param \Magento\Module\ModuleListInterface $moduleList
     * @param \Magento\Module\ResourceInterface $resourceResource
     * @param \Magento\Module\Setup\MigrationFactory $migrationFactory
     * @param \Magento\Encryption\EncryptorInterface $encryptor
     * @param \Magento\App\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Logger $logger,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\App\Resource $resource,
        \Magento\Module\Dir\Reader $modulesReader,
        \Magento\Module\ModuleListInterface $moduleList,
        \Magento\Module\ResourceInterface $resourceResource,
        \Magento\Module\Setup\MigrationFactory $migrationFactory,
        \Magento\Encryption\EncryptorInterface $encryptor,
        \Magento\App\Filesystem $filesystem
    ) {
        $this->_logger = $logger;
        $this->_eventManager = $eventManager;
        $this->_resourceModel = $resource;
        $this->_modulesReader = $modulesReader;
        $this->_moduleList = $moduleList;
        $this->_resourceResource = $resourceResource;
        $this->_migrationFactory = $migrationFactory;
        $this->_encryptor = $encryptor;
        $this->filesystem = $filesystem;
    }

    /**
     * @return \Magento\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * @return \Magento\Logger $logger
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return \Magento\Module\ModuleListInterface
     */
    public function getModuleList()
    {
        return $this->_moduleList;
    }

    /**
     * @return \Magento\Module\Dir\Reader
     */
    public function getModulesReader()
    {
        return $this->_modulesReader;
    }

    /**
     * @return \Magento\App\Resource
     */
    public function getResourceModel()
    {
        return $this->_resourceModel;
    }

    /**
     * @return \Magento\Module\Setup\MigrationFactory
     */
    public function getMigrationFactory()
    {
        return $this->_migrationFactory;
    }

    /**
     * @return \Magento\Module\ResourceInterface
     */
    public function getResourceResource()
    {
        return $this->_resourceResource;
    }

    /**
     * @return \Magento\Encryption\EncryptorInterface
     */
    public function getEncryptor()
    {
        return $this->_encryptor;
    }

    /**
     * @return \Magento\App\Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }
}
