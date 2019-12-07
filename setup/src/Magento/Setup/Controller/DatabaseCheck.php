<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Setup\Validator\DbValidator;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Class DatabaseCheck
 */
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
            $driverOptions = [];
            if ($this->isDriverOptionsGiven($params)) {
                if (empty($params['driverOptionsSslVerify'])) {
                    $params['driverOptionsSslVerify'] = 0;
                }
                $driverOptions = [
                    ConfigOptionsListConstants::KEY_MYSQL_SSL_KEY => $params['driverOptionsSslKey'],
                    ConfigOptionsListConstants::KEY_MYSQL_SSL_CERT => $params['driverOptionsSslCert'],
                    ConfigOptionsListConstants::KEY_MYSQL_SSL_CA => $params['driverOptionsSslCa'],
                    ConfigOptionsListConstants::KEY_MYSQL_SSL_VERIFY => (int) $params['driverOptionsSslVerify'],
                ];
            }
            $this->dbValidator->checkDatabaseConnectionWithDriverOptions(
                $params['name'],
                $params['host'],
                $params['user'],
                $password,
                $driverOptions
            );
            $tablePrefix = isset($params['tablePrefix']) ? $params['tablePrefix'] : '';
            $this->dbValidator->checkDatabaseTablePrefix($tablePrefix);
            return new JsonModel(['success' => true]);
        } catch (\Exception $e) {
            return new JsonModel(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Is Driver Options Given
     *
     * @param array $params
     * @return bool
     */
    private function isDriverOptionsGiven($params)
    {
        return !(
            empty($params['driverOptionsSslKey']) ||
            empty($params['driverOptionsSslCert']) ||
            empty($params['driverOptionsSslCa'])
        );
    }
}
