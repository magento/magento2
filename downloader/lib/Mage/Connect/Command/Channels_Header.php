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
        'list-channels' => array(
            'summary' => 'List Available Channels',
            'function' => 'doList',
            'shortcut' => 'lc',
            'options' => array(),
            'doc' => '
List all available channels for installation.
',
            ),
        'channel-delete' => array(
            'summary' => 'Remove a Channel From the List',
            'function' => 'doDelete',
            'shortcut' => 'cde',
            'options' => array(),
            'doc' => '<channel name>
Delete a channel from the registry.  You may not
remove any channel that has installed packages.
'
            ),
        'channel-add' => array(
            'summary' => 'Add a Channel',
            'function' => 'doAdd',
            'shortcut' => 'ca',
            'options' => array(),
            'doc' => '<channel.xml>
Add a private channel to the channel list.  Note that all
public channels should be synced using "update-channels".
Parameter may be either a local file or remote URL to a
channel.xml.
'
            ),
        'channel-info' => array(
            'summary' => 'Retrieve Information on a Channel',
            'function' => 'doInfo',
            'shortcut' => 'ci',
            'options' => array(),
            'doc' => '<package>
List the files in an installed package.
'
            ),
        'channel-alias' => array(
            'summary' => 'Specify an alias to a channel name',
            'function' => 'doAlias',
            'shortcut' => 'cha',
            'options' => array(),
            'doc' => '<channel> <alias>
Specify a specific alias to use for a channel name.
The alias may not be an existing channel name or
alias.
'
            ),
        'channel-login' => array(
            'summary' => 'Connects and authenticates to remote channel server',
            'shortcut' => 'cli',
            'function' => 'doLogin',
            'options' => array(),
            'doc' => '<channel name>
Log in to a remote channel server.  If <channel name> is not supplied,
the default channel is used. To use remote functions in the installer
that require any kind of privileges, you need to log in first.  The
username and password you enter here will be stored in your per-user
PEAR configuration (~/.pearrc on Unix-like systems).  After logging
in, your username and password will be sent along in subsequent
operations on the remote server.',
            ),
        'channel-logout' => array(
            'summary' => 'Logs out from the remote channel server',
            'shortcut' => 'clo',
            'function' => 'doLogout',
            'options' => array(),
            'doc' => '<channel name>
Logs out from a remote channel server.  If <channel name> is not supplied,
the default channel is used. This command does not actually connect to the
remote server, it only deletes the stored username and password from your user
configuration.',
            ),
        );
