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
namespace Magento\Cron\Model\Config\Converter;

/**
 * Convert data incoming from data base storage
 */
class Db implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert data
     *
     * @param array $source
     * @return array
     */
    public function convert($source)
    {
        $cronTab = isset($source['crontab']) ? $source['crontab'] : array();

        if (empty($cronTab)) {
            return $cronTab;
        }
        return $this->_extractParams($cronTab);
    }

    /**
     * Extract and prepare cron job data
     *
     * @param array $jobs
     * @return array
     */
    protected function _extractParams(array $cronTab)
    {
        $result = array();
        foreach ($cronTab as $groupName => $groupConfig) {
            $jobs = $groupConfig['jobs'];
            foreach ($jobs as $jobName => $value) {
                $result[$groupName][$jobName] = $value;

                if (isset($value['schedule']) && is_array($value['schedule'])) {
                    $this->_processConfigParam($value, $jobName, $result[$groupName]);
                    $this->_processScheduleParam($value, $jobName, $result[$groupName]);
                }

                $this->_processRunModel($value, $jobName, $result[$groupName]);
            }
        }
        return $result;
    }

    /**
     * Fetch parameter 'config_path' from 'schedule' container
     *
     * @param array  $jobConfig
     * @param string $jobName
     * @param array  $result
     * @return void
     */
    protected function _processConfigParam(array $jobConfig, $jobName, array &$result)
    {
        if (array_key_exists('config_path', $jobConfig['schedule'])) {
            $result[$jobName]['config_path'] = $jobConfig['schedule']['config_path'];
        }
    }

    /**
     * Fetch parameter 'cron_expr' from 'schedule' container, reassign it
     *
     * @param array  $jobConfig
     * @param string $jobName
     * @param array  $result
     * @return void
     */
    protected function _processScheduleParam(array $jobConfig, $jobName, array &$result)
    {
        if (array_key_exists('cron_expr', $jobConfig['schedule'])) {
            $result[$jobName]['schedule'] = $jobConfig['schedule']['cron_expr'];
        }
    }

    /**
     * Fetch parameters from 'run' container and save it by reference
     *
     * @param array  $jobConfig
     * @param string $jobName
     * @param array  $result
     * @return void
     */
    protected function _processRunModel(array $jobConfig, $jobName, array &$result)
    {
        if (isset($jobConfig['run']) && is_array($jobConfig['run']) && array_key_exists('model', $jobConfig['run'])) {
            $callPath = explode('::', $jobConfig['run']['model']);

            if (empty($callPath) || empty($callPath[0]) || empty($callPath[1])) {
                unset($result[$jobName]['run']);
                return;
            }

            $result[$jobName]['instance'] = $callPath[0];
            $result[$jobName]['method'] = $callPath[1];
            unset($result[$jobName]['run']);
        }
    }
}
