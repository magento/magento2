<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Setup\Controller;

use Magento\Framework\Filesystem;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Model\WebLogger;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class DatabaseCheck extends AbstractActionController
{
    /**
     * Installer service factory
     *
     * @var \Magento\Setup\Model\InstallerFactory
     */
    private $installerFactory;

    /**
     * Filesystem to access log
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Constructor
     *
     * @param InstallerFactory $installerFactory
     * @param Filesystem $filesystem
     */
    public function __construct(InstallerFactory $installerFactory, Filesystem $filesystem)
    {
        $this->installerFactory = $installerFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * Result of checking DB credentials
     *
     * @return JsonModel
     */
    public function indexAction()
    {
        $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        try {
            $installer = $this->installerFactory->create(new WebLogger($this->filesystem));
            $password = isset($params['password']) ? $params['password'] : '';
            $installer->checkDatabaseConnection($params['name'], $params['host'], $params['user'], $password);
            return new JsonModel(['success' => true]);
        } catch (\Exception $e) {
            return new JsonModel(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
