<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Framework\App\MaintenanceMode;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

/**
 * Class \Magento\Setup\Controller\Maintenance
 *
 * @since 2.0.0
 */
class Maintenance extends AbstractActionController
{
    /**
     * Handler for maintenance mode
     *
     * @var MaintenanceMode
     * @since 2.0.0
     */
    private $maintenanceMode;

    /**
     * Constructor
     *
     * @param MaintenanceMode $maintenanceMode
     * @since 2.0.0
     */
    public function __construct(MaintenanceMode $maintenanceMode)
    {
        $this->maintenanceMode = $maintenanceMode;
    }

    /**
     * Puts store in maintenance mode
     *
     * @return JsonModel
     * @since 2.0.0
     */
    public function indexAction()
    {
        try {
            $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
            $action = isset($params['disable']) && $params['disable'] ? false : true;
            $this->maintenanceMode->set($action);
            return new JsonModel(['responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS]);
        } catch (\Exception $e) {
            return new JsonModel(
                [
                    'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
                    'error' => $e->getMessage()
                ]
            );
        }
    }
}
