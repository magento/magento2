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

return array(
    'application' => array(
        'url_host' => '127.0.0.1',
        'url_path' => '/',
        'admin' => array('frontname' => 'backend', 'username' => 'admin', 'password' => 'password1'),
        'installation' => array('options' => array('option1' => 'value 1', 'option2' => 'value 2'))
    ),
    'scenario' => array(
        'common_config' => array(
            'arguments' => array('arg1' => 'value 1', 'arg2' => 'value 2'),
            'settings' => array('setting1' => 'setting 1', 'setting2' => 'setting 2'),
            'fixtures' => array('fixture2.php')
        ),
        'scenarios' => array(
            'Scenario' => array(
                'file' => 'scenario.jmx',
                'arguments' => array(
                    'arg2' => 'overridden value 2',
                    'arg3' => 'custom value 3',
                    \Magento\TestFramework\Performance\Scenario::ARG_HOST => 'no crosscutting params'
                ),
                'settings' => array('setting2' => 'overridden setting 2', 'setting3' => 'setting 3'),
                'fixtures' => array('fixture.php')
            ),
            'Scenario with Error' => array('file' => 'scenario_error.jmx'),
            'Scenario with Failure' => array(
                'file' => 'scenario_failure.jmx',
                'settings' => array(\Magento\TestFramework\Performance\Testsuite::SETTING_SKIP_WARM_UP => true)
            )
        )
    ),
    'report_dir' => 'report'
);
