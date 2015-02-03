<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Setup\Model\ObjectManagerFactory;
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
     * Result of checking constrains for enabling/disabling modules
     *
     * @return JsonModel
     */
    public function indexAction()
    {
        $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $enabledModules = isset($params['selectedModules']) ? $params['selectedModules'] : [];
        $status = $this->objectManager->create('Magento\Framework\Module\Status');

        // checking constraints
        $constraints = $status->checkConstraints(true, $enabledModules, true);
        if ($constraints) {
            $message = $this->handleConstraints(true, $constraints);
            return new JsonModel(['success' => false, 'error' => $message]);
        }

        return new JsonModel(['success' => true]);
    }

    /**
     * Handles constraints
     *
     * @param bool $isEnable
     * @param string $constraints
     * @return string
     */
    private function handleConstraints($isEnable, $constraints)
    {
        if ($isEnable) {
            $updateType = 'enable';
        } else {
            $updateType = 'disable';
        }
        $message = " Unable to $updateType modules because of the following constraints:\n"
            . implode("<br />", $constraints);
        return $message;
    }
}
