<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Framework\Module\Status;
use Magento\Setup\Model\DependencyReadinessCheck;
use Magento\Setup\Model\ModuleStatusFactory;
use Magento\Setup\Model\UninstallDependencyCheck;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Class DependencyCheck
 *
 * Checks dependencies.
 * @since 2.0.0
 */
class DependencyCheck extends AbstractActionController
{
    /**
     * Dependency Readiness Check
     *
     * @var DependencyReadinessCheck
     * @since 2.0.0
     */
    protected $dependencyReadinessCheck;

    /**
     * Uninstall Dependency Readiness Check
     *
     * @var UninstallDependencyCheck
     * @since 2.0.0
     */
    protected $uninstallDependencyCheck;

    /**
     * Module/Status Object
     *
     * @var Status
     * @since 2.0.0
     */
    protected $moduleStatus;

    /**
     * Constructor
     *
     * @param DependencyReadinessCheck $dependencyReadinessCheck
     * @param UninstallDependencyCheck $uninstallDependencyCheck
     * @param ModuleStatusFactory $moduleStatusFactory
     * @since 2.0.0
     */
    public function __construct(
        DependencyReadinessCheck $dependencyReadinessCheck,
        UninstallDependencyCheck $uninstallDependencyCheck,
        ModuleStatusFactory $moduleStatusFactory
    ) {
        $this->dependencyReadinessCheck = $dependencyReadinessCheck;
        $this->uninstallDependencyCheck = $uninstallDependencyCheck;
        $this->moduleStatus = $moduleStatusFactory->create();
    }

    /**
     * Verifies component dependency
     *
     * @return JsonModel
     * @since 2.0.0
     */
    public function componentDependencyAction()
    {
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;
        $packages = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $data = [];
        foreach ($packages as $package) {
            $data[] = implode(' ', $package);
        }
        $dependencyCheck = $this->dependencyReadinessCheck->runReadinessCheck($data);
        $data = [];
        if (!$dependencyCheck['success']) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
            $data['errorMessage'] = $dependencyCheck['error'];
        }
        $data['responseType'] = $responseType;
        return new JsonModel($data);
    }

    /**
     * Verifies component dependency for uninstall
     *
     * @return JsonModel
     * @since 2.0.0
     */
    public function uninstallDependencyCheckAction()
    {
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;
        $packages = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);

        $packagesToDelete = [];
        foreach ($packages as $package) {
            $packagesToDelete[] = $package['name'];
        }

        $dependencyCheck = $this->uninstallDependencyCheck->runUninstallReadinessCheck($packagesToDelete);
        $data = [];
        if (!$dependencyCheck['success']) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
            $data['errorMessage'] = $dependencyCheck['error'];
        }
        $data['responseType'] = $responseType;
        return new JsonModel($data);
    }

    /**
     * Verifies component dependency for enable/disable actions
     *
     * @return JsonModel
     * @since 2.0.0
     */
    public function enableDisableDependencyCheckAction()
    {
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;
        $data = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);

        try {
            if (empty($data['packages'])) {
                throw new \Exception('No packages have been found.');
            }

            if (empty($data['type'])) {
                throw new \Exception('Can not determine the flow.');
            }

            $modules = $data['packages'];

            $isEnable = ($data['type'] !== 'disable');

            $modulesToChange = [];
            foreach ($modules as $module) {
                if (!isset($module['name'])) {
                    throw new \Exception('Can not find module name.');
                }
                $modulesToChange[] = $module['name'];
            }

            $constraints = $this->moduleStatus->checkConstraints($isEnable, $modulesToChange);
            $data = [];

            if ($constraints) {
                $data['errorMessage'] = "Unable to change status of modules because of the following constraints: "
                    . implode("<br>", $constraints);
                $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
            }
        } catch (\Exception $e) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
            $data['errorMessage'] = $e->getMessage();
        }

        $data['responseType'] = $responseType;
        return new JsonModel($data);
    }
}
