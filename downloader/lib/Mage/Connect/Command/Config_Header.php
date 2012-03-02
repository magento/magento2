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
        'config-show' => array(
            'summary' => 'Show All Settings',
            'function' => 'doConfigShow',
            'shortcut' => 'csh',
            'options' => array(
                'channel' => array(
                    'shortopt' => 'c',
                    'doc' => 'show configuration variables for another channel',
                    'arg' => 'CHAN',
),
),
            'doc' => '[layer]
Displays all configuration values.  An optional argument
may be used to tell which configuration layer to display.  Valid
configuration layers are "user", "system" and "default". To display
configurations for different channels, set the default_channel
configuration variable and run config-show again.
',
),
        'config-get' => array(
            'summary' => 'Show One Setting',
            'function' => 'doConfigGet',
            'shortcut' => 'cg',
            'options' => array(
                'channel' => array(
                    'shortopt' => 'c',
                    'doc' => 'show configuration variables for another channel',
                    'arg' => 'CHAN',
),
),
            'doc' => '<parameter> [layer]
Displays the value of one configuration parameter.  The
first argument is the name of the parameter, an optional second argument
may be used to tell which configuration layer to look in.  Valid configuration
layers are "user", "system" and "default".  If no layer is specified, a value
will be picked from the first layer that defines the parameter, in the order
just specified.  The configuration value will be retrieved for the channel
specified by the default_channel configuration variable.
',
),
        'config-set' => array(
            'summary' => 'Change Setting',
            'function' => 'doConfigSet',
            'shortcut' => 'cs',
            'options' => array(
                'channel' => array(
                    'shortopt' => 'c',
                    'doc' => 'show configuration variables for another channel',
                    'arg' => 'CHAN',
),
),
            'doc' => '<parameter> <value> [layer]
Sets the value of one configuration parameter.  The first argument is
the name of the parameter, the second argument is the new value.  Some
parameters are subject to validation, and the command will fail with
an error message if the new value does not make sense.  An optional
third argument may be used to specify in which layer to set the
configuration parameter.  The default layer is "user".  The
configuration value will be set for the current channel, which
is controlled by the default_channel configuration variable.
',
),
        'config-help' => array(
            'summary' => 'Show Information About Setting',
            'function' => 'doConfigHelp',
            'shortcut' => 'ch',
            'options' => array(),
            'doc' => '[parameter]
Displays help for a configuration parameter.  Without arguments it
displays help for all configuration parameters.
',
),
);
