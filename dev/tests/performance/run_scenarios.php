<?php
/**
 * JMeter scenarios execution script
 *
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
 * @category    Magento
 * @package     performance_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

try {
    /** @var $config Magento_Config */
    $config = require_once __DIR__ . '/framework/bootstrap.php';

    $adminOptions = $config->getAdminOptions();
    $scenario = new Magento_Scenario(new Magento_Shell(true), $config->getJMeterPath(), $config->getReportDir());
    $scenarioParamsGlobal = array(
        Magento_Scenario::PARAM_HOST => $config->getApplicationUrlHost(),
        Magento_Scenario::PARAM_PATH => $config->getApplicationUrlPath(),
        Magento_Scenario::PARAM_ADMIN_FRONTNAME => $adminOptions['frontname'],
        Magento_Scenario::PARAM_ADMIN_USERNAME => $adminOptions['username'],
        Magento_Scenario::PARAM_ADMIN_PASSWORD => $adminOptions['password'],
    );
    $scenarioTotalCount = count($config->getScenarios());
    $scenarioFailCount = 0;
    $scenarioNum = 1;
    foreach ($config->getScenarios() as $scenarioFile => $scenarioParams) {
        echo "Scenario $scenarioNum of $scenarioTotalCount: '$scenarioFile'" . PHP_EOL;
        $scenarioParams = array_merge($scenarioParams, $scenarioParamsGlobal);
        try {
            $scenario->run($scenarioFile, $scenarioParams);
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            $scenarioFailCount++;
        }
        echo PHP_EOL;
        $scenarioNum++;
    }
    if ($scenarioFailCount) {
        throw new Magento_Exception("Failed $scenarioFailCount of $scenarioTotalCount scenario(s)");
    }
    echo 'Successful' . PHP_EOL;
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}
