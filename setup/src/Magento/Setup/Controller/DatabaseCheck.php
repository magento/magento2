<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Setup\Validator\DbValidator;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class DatabaseCheck extends AbstractActionController
{
    /**
     * @var DbValidator
     */
    private $dbValidator;

    /**
     * Constructor
     *
     * @param DbValidator $dbValidator
     */
    public function __construct(DbValidator $dbValidator)
    {
        $this->dbValidator = $dbValidator;
    }

    /**
     * Result of checking DB credentials
     *
     * @return JsonModel
     */
    public function indexAction()
    {
        try {
            $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
            $password = isset($params['password']) ? $params['password'] : '';
            $this->dbValidator->checkDatabaseConnection($params['name'], $params['host'], $params['user'], $password);
            $tablePrefix = isset($params['tablePrefix']) ? $params['tablePrefix'] : '';
            $this->dbValidator->checkDatabaseTablePrefix($tablePrefix);
            return new JsonModel(['success' => true]);
        } catch (\Exception $e) {
            return new JsonModel(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
