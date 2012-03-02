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
 * @category    Mage
 * @package     Mage_Connect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

    $commands = array(
        'list-installed' => array(
            'summary' => 'List Installed Packages In The Default Channel',
            'function' => 'doList',
            'shortcut' => 'l',
            'options' => array(
                'channel' => array(
                    'shortopt' => 'c',
                    'doc' => 'list installed packages from this channel',
                    'arg' => 'CHAN',
                    ),
                'allchannels' => array(
                    'shortopt' => 'a',
                    'doc' => 'list installed packages from all channels',
                    ),
                ),
            'doc' => '<package>
If invoked without parameters, this command lists the PEAR packages
installed in your php_dir ({config php_dir}).  With a parameter, it
lists the files in a package.
',
            ),
        'list-files' => array(
            'summary' => 'List Files In Installed Package',
            'function' => 'doFileList',
            'shortcut' => 'fl',
            'options' => array(),
            'doc' => '<package>
List the files in an installed package.
'
            ),
        'info' => array(
            'summary'  => 'Display information about a package',
            'function' => 'doInfo',
            'shortcut' => 'in',
            'options'  => array(),
            'doc'      => '<package>
Displays information about a package. The package argument may be a
local package file, an URL to a package file, or the name of an
installed package.'
            ),
        'sync' => array(
            'summary'  => 'Synchronize Manually Installed Packages',
            'function' => 'doSync',
            'shortcut' => 'snc',
            'options'  => array(),
            'doc'      => '<package>
Synchronize manually installed package info with local cache.'
            ),
        'sync-pear' => array(
            'summary'  => 'Synchronize already Installed Packages by pear',
            'function' => 'doSyncPear',
            'shortcut' => 'sncp',
            'options'  => array(),
            'doc'      => '<package>
Synchronize already Installed Packages by pear.'
            )
        );
