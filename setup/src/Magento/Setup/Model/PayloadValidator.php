<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

/**
 * Validates payloads for updater tasks
 */
class PayloadValidator
{
    /**
     * @var \Magento\Framework\Module\FullModuleList
     */
    private $moduleList;

    /**
     * @param \Magento\Framework\Module\FullModuleList $moduleList
     */
    public function __construct(\Magento\Framework\Module\FullModuleList $moduleList)
    {
        $this->moduleList = $moduleList;
    }

    /**
     * Validate POST request payload
     *
     * @param array $postPayload
     * @return string
     */
    public function validatePayload(array $postPayload)
    {
        $jobType = $postPayload[UpdaterTaskCreator::KEY_POST_JOB_TYPE];
        $errorMessage = '';
        switch ($jobType) {
            case 'uninstall':
                $errorMessage = $this->validateUninstallPayload($postPayload);
                break;

            case 'update':
                $errorMessage = $this->validateUpdatePayload($postPayload);
                break;

            case 'enable':
            case 'disable':
                $errorMessage = $this->validateEnableDisablePayload($postPayload);
                break;
        }
        return $errorMessage;
    }

    /**
     * Validate 'uninstall' job type payload
     *
     * @param array $postPayload
     * @return string
     */
    private function validateUninstallPayload(array $postPayload)
    {
        $errorMessage = '';
        if (!isset($postPayload[UpdaterTaskCreator::KEY_POST_DATA_OPTION])) {
            $errorMessage = 'Missing dataOption' . PHP_EOL;
        }
        return $errorMessage;
    }

    /**
     * Validate 'update' job type payload
     *
     * @param array $postPayload
     * @return string
     */
    private function validateUpdatePayload(array $postPayload)
    {
        $errorMessage = '';
        if (!isset($postPayload[UpdaterTaskCreator::KEY_POST_PACKAGES])) {
            $errorMessage = 'Missing packages' . PHP_EOL;
        } else {
            $packages = $postPayload[UpdaterTaskCreator::KEY_POST_PACKAGES];
            foreach ($packages as $package) {
                if ((!isset($package[UpdaterTaskCreator::KEY_POST_PACKAGE_NAME]))
                    || (!isset($package[UpdaterTaskCreator::KEY_POST_PACKAGE_VERSION]))
                ) {
                    $errorMessage .= 'Missing package information' . PHP_EOL;
                    break;
                }
            }
        }
        return $errorMessage;
    }

    /**
     * Validate 'enable/disable' job type payload
     *
     * @param array $postPayload
     * @return string
     */
    private function validateEnableDisablePayload(array $postPayload)
    {
        $errorMessage = '';
        if (!isset($postPayload[UpdaterTaskCreator::KEY_POST_PACKAGES])) {
            $errorMessage = 'Missing packages' . PHP_EOL;
        } else {
            $packages = $postPayload[UpdaterTaskCreator::KEY_POST_PACKAGES];
            foreach ($packages as $package) {
                if (!$this->moduleList->has($package[UpdaterTaskCreator::KEY_POST_PACKAGE_NAME])) {
                    $errorMessage .= 'Invalid Magento module name: '
                        . $package[UpdaterTaskCreator::KEY_POST_PACKAGE_NAME] . PHP_EOL;
                    break;
                }
            }
        }
        return $errorMessage;
    }
}
