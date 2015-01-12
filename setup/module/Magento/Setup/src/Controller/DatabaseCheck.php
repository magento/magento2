<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

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
     * WebLogger to access log
     *
     * @var WebLogger
     */
    private $webLogger;

    /**
     * Constructor
     *
     * @param InstallerFactory $installerFactory
     * @param WebLogger $webLogger
     */
    public function __construct(InstallerFactory $installerFactory, WebLogger $webLogger)
    {
        $this->installerFactory = $installerFactory;
        $this->webLogger = $webLogger;
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
            $installer = $this->installerFactory->create($this->webLogger);
            $password = isset($params['password']) ? $params['password'] : '';
            $installer->checkDatabaseConnection($params['name'], $params['host'], $params['user'], $password);
            return new JsonModel(['success' => true]);
        } catch (\Exception $e) {
            return new JsonModel(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
