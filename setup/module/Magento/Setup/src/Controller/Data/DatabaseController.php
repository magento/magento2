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
namespace Magento\Setup\Controller\Data;

use Magento\Setup\Model\InstallerFactory;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Magento\Setup\Model\WebLogger;

class DatabaseController extends AbstractActionController
{
    /**
     * JSON response object
     *
     * @var JsonModel
     */
    private $jsonResponse;

    /**
     * Installer service factory
     *
     * @var \Magento\Setup\Model\InstallerFactory
     */
    private $installerFactory;

    /**
     * Constructor
     *
     * @param JsonModel $response
     * @param InstallerFactory $installerFactory
     */
    public function __construct(JsonModel $response, InstallerFactory $installerFactory)
    {
        $this->jsonResponse = $response;
        $this->installerFactory = $installerFactory;
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
            $installer = $this->installerFactory->create(new WebLogger);
            $password = isset($params['password']) ? $params['password'] : '';
            $installer->checkDatabaseConnection($params['name'], $params['host'], $params['user'], $password);
            return $this->jsonResponse->setVariables(['success' => true]);
        } catch (\Exception $e) {
            return $this->jsonResponse->setVariables(['success' => false, 'error' => $e->getMessage()]);
        }
    }

}
