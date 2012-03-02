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
 * @category    Magento
 * @package     Mage_Customer
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$configXml = <<<EOD
<config>
    <global>
        <customer>
            <address>
                <formats>
                    <escaped_one translate="title">
                        <title>ESCAPED_ONE</title>
                        <escapeHtml>true</escapeHtml>
                    </escaped_one>
                    <escaped_two translate="title">
                        <title>ESCAPED_TWO</title>
                        <escapeHtml>no</escapeHtml>
                    </escaped_two>
                    <escaped_three translate="title">
                        <title>ESCAPED_THREE</title>
                        <escapeHtml>false</escapeHtml>
                    </escaped_three>
                    <escaped_four translate="title">
                        <title>ESCAPED_FOUR</title>
                        <escapeHtml>0</escapeHtml>
                    </escaped_four>
                    <escaped_five translate="title">
                        <title>ESCAPED_FIVE</title>
                        <escapeHtml></escapeHtml>
                    </escaped_five>
                    <escaped_six translate="title">
                        <title>ESCAPED_SIX</title>
                        <escapeHtml>1</escapeHtml>
                    </escaped_six>
                </formats>
            </address>
        </customer>
    </global>
</config>
EOD;

$config = new Mage_Core_Model_Config_Base($configXml);
Mage::getConfig()->extend($config);
