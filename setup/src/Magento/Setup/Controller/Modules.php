<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Setup\Model\ModuleStatus;
use Magento\Setup\Model\ObjectManagerProvider;
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
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(ModuleStatus $allModules, ObjectManagerProvider $objectManagerProvider)
    {
        $this->allModules = $allModules;
        $this->objectManager = $objectManagerProvider->get();
    }

    /**
     * Returns list of Modules
     *
     * @return JsonModel
     */
    public function indexAction()
    {
        $allModules = $this->allModules->getAllModules();
        $enabledModules = [];
        foreach ($allModules as $module) {
            if ($module['selected']) {
                $enabledModules[] = $module['name'];
            }
        }
        $validity = $this->checkGraph($enabledModules);
        ksort($allModules);
        if ($validity->getVariable("success")) {
            return new JsonModel(['success' => true, 'modules' => $allModules]);
        } else {
            $errorMessage = $validity->getVariable("error");
            return new JsonModel(['success' => false, 'modules' => $allModules,
                'error' => '<b> Corrupt config.php!</b> <br />' . $errorMessage]);
        }
    }

    /**
     * Result of checking Modules Validity
     *
     * @return JsonModel
     */
    public function allModulesValidAction()
    {
        try {
            $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
            $enabledModules = isset($params['selectedModules']) ? $params['selectedModules'] : [];
            return $this->checkGraph($enabledModules);
        } catch (\Exception $e) {
            return new JsonModel(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Checks validity of enabling/disabling modules.
     *
     * @param array $toBeEnabledModules
     * @param bool $prettyFormat
     * @return JsonModel
     */
    private function checkGraph(array $toBeEnabledModules, $prettyFormat = false)
    {
        $status = $this->objectManager->create('Magento\Framework\Module\Status');

        // checking enabling constraints
        $constraints = $status->checkConstraints(true, $toBeEnabledModules, [], $prettyFormat);
        if ($constraints) {
            $message = $this->getConstraintsFailureMessage(true, $constraints);
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

        $constraints = $status->checkConstraints(
            $params['status'],
            [$params['module']],
            array_diff($params['selectedModules'], [$params['module']])
        );
        if ($constraints) {
            $message = $this->getConstraintsFailureMessage($params['status'], $constraints);
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
     * @param string[] $constraints
     * @return string
     */
    private function getConstraintsFailureMessage($isEnable, array $constraints)
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
