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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$commands = array(
        'list-upgrades' => array(
            'summary' => 'List Available Upgrades',
            'function' => 'doListUpgrades',
            'shortcut' => 'lu',
            'options' => array(
                'channelinfo' => array(
                    'shortopt' => 'i',
                    'doc' => 'output fully channel-aware data, even on failure',
                    ),
            ),
            'doc' => '[preferred_state]
List releases on the server of packages you have installed where
a newer version is available with the same release state (stable etc.)
or the state passed as the second parameter.'
            ),
        'list-available' => array(
            'summary' => 'List Available Packages',
            'function' => 'doListAvailable',
            'shortcut' => 'la',
            'options' => array(
                'channel' =>
                    array(
                    'shortopt' => 'c',
                    'doc' => 'specify a channel other than the default channel',
                    'arg' => 'CHAN',
                    ),
                'channelinfo' => array(
                    'shortopt' => 'i',
                    'doc' => 'output fully channel-aware data, even on failure',
                    ),
                ),
            'doc' => '
Lists the packages available on the configured server along with the
latest stable release of each package.',
            ),
        'download' => array(
            'summary' => 'Download Package',
            'function' => 'doDownload',
            'shortcut' => 'd',
            'options' => array(
                'nocompress' => array(
                    'shortopt' => 'Z',
                    'doc' => 'download an uncompressed (.tar) file',
                    ),
                ),
            'doc' => '<package>...
Download package tarballs.  The files will be named as suggested by the
server, for example if you download the DB package and the latest stable
version of DB is 1.6.5, the downloaded file will be DB-1.6.5.tgz.',
            ),
        'clear-cache' => array(
            'summary' => 'Clear Web Services Cache',
            'function' => 'doClearCache',
            'shortcut' => 'cc',
            'options' => array(),
            'doc' => '
Clear the XML-RPC/REST cache.  See also the cache_ttl configuration
parameter.
',
            ),
        );
