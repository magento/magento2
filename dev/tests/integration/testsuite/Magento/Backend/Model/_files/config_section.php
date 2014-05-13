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
    array(
        'section' => 'dev',
        'groups' => array(
            'log' => array(
                'fields' => array(
                    'active' => array('value' => '1'),
                    'file' => array('value' => 'fileName.log'),
                    'exception_file' => array('value' => 'exceptionFileName.log')
                )
            ),
            'debug' => array(
                'fields' => array(
                    'template_hints' => array('value' => '1'),
                    'template_hints_blocks' => array('value' => '0')
                )
            )
        ),
        'expected' => array(
            'dev/log' => array(
                'dev/log/active' => '1',
                'dev/log/file' => 'fileName.log',
                'dev/log/exception_file' => 'exceptionFileName.log'
            ),
            'dev/debug' => array('dev/debug/template_hints' => '1', 'dev/debug/template_hints_blocks' => '0')
        )
    )
);
