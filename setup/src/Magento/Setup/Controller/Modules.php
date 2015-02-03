<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Setup\Model\ModuleStatus;
use Magento\Setup\Model\ObjectManagerFactory;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

class Modules extends AbstractActionController
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ModuleStatus
     */
    protected $allModules;

    /**
     * @param ModuleStatus $allModules
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(ModuleStatus $allModules, ObjectManagerFactory $objectManagerFactory)
    {
        $this->allModules = $allModules;
        $this->objectManager = $objectManagerFactory->create();
    }

    /**
     * @return JsonModel
     */
    public function indexAction()
    {
        $allModules = $this->allModules->getAllModules(null, false);
        $enabledModules =[];
        foreach ($allModules as $module ) {
            if ($module['selected']) {
                $enabledModules [] = $module['name'];
            }
        }
        $validity = $this->checkGraph($enabledModules, array_keys($allModules), true);
        ksort($allModules);
        if ($validity->getVariable("success")) {
            return new JsonModel(['success' => true, 'modules' => $allModules ]);
        } else {
            $errorMessage = $validity->getVariable("error");
            return new JsonModel(['success' => false,  'modules' => $allModules,
                'error' => '<b> Corrupt config.php!</b> <br />' . $errorMessage ]);
        }
    }

    /**
     * Result of checking Modules Validity
     *
     * @return JsonModel
     */
    public function allModulesValidAction()
    {
        $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $enabledModules = isset($params['selectedModules']) ? $params['selectedModules'] : [];
        $allModules = isset($params['allModules']) ? $params['allModules'] : [];
        return $this->checkGraph($enabledModules, $allModules);
    }

    /**
     * @param [] $enabledModules
     * @param [] $allModules
     * @param bool $prettyFormat
     * @return JsonModel
     */
    private function checkGraph($enabledModules, $allModules, $prettyFormat = false)
    {
        $status = $this->objectManager->create('Magento\Framework\Module\Status');

        // checking enabling constraints
        $constraints = $status->checkConstraints(true, $enabledModules, $allModules, $prettyFormat);
        if ($constraints) {
            $message = $this->handleConstraints(true, $constraints);
            return new JsonModel(['success' => false, 'error' => $message]);
        }

        // checking disabling constraints
        $constraints = $status->checkConstraints(
            false, array_diff($allModules, $enabledModules), $allModules, $prettyFormat
        );
        if ($constraints) {
            $message = $this->handleConstraints(false, $constraints);
            return new JsonModel(['success' => false, 'error' => $message]);
        }

        return new JsonModel(['success' => true]);
    }

    /**
     * Check Module Dependencies
     *
     * @return JsonModel
     */
    public function validateAction()
    {
        $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $status = $this->objectManager->create('Magento\Framework\Module\Status');

        $constraints = $status->checkConstraints($params['status'], [$params['module']], $params['selectedModules']);
        if ($constraints) {
            $message = $this->handleConstraints($params['status'], $constraints);
            return new JsonModel(['success' => false, 'error' => $message]);
        }

        $this->allModules->setIsEnabled($params['status'], $params['module']);
        $allModules = $this->allModules->getAllModules($params['selectedModules']);
        ksort($allModules);
        return new JsonModel(['modules' => $allModules]);
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
        $message = " Unable to $updateType modules because of the following constraints:<br>" . PHP_EOL
            . implode("<br>" . PHP_EOL, $constraints);
        return $message;
    }
}
