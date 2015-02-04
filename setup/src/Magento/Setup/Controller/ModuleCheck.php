<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Setup\Model\ObjectManagerFactory;
use Magento\Framework\Module\Status;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class ModuleCheck extends AbstractActionController
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(ObjectManagerFactory $objectManagerFactory)
    {
        $this->objectManager = $objectManagerFactory->create();
    }

    /**
     * Result of checking constraints for enabling/disabling modules
     *
     * @return JsonModel
     */
    public function indexAction()
    {
        $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $enabledModules = isset($params['selectedModules']) ? $params['selectedModules'] : [];
        $allModules = isset($params['allModules']) ? $params['allModules'] : [];
        if (empty($enabledModules)) {
            return new JsonModel(['success' => false, 'error' => 'You cannot disable all modules.']);
        }
        $status = $this->objectManager->create('Magento\Framework\Module\Status');
        // checking constraints
        $constraints = $status->checkConstraints(false, array_diff($allModules, $enabledModules), Status::MODE_ENABLED);
        if ($constraints) {
            $message = " Unable to disable modules because of the following constraints:\n"
                . implode("<br />", $constraints);
            return new JsonModel(['success' => false, 'error' => $message]);
        }
        return new JsonModel(['success' => true]);
    }
}
