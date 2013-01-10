<?php
/**
 * The list of all expected soap fault XMLs.
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    'expectedResultArrayDataDetails' =>
    '<?xml version="1.0" encoding="utf-8" ?>
    <env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope">
        <env:Body>
            <env:Fault>
                <env:Code>
                    <env:Value>env:Sender</env:Value>
                </env:Code>
                <env:Reason>
                    <env:Text xml:lang="cn">Fault reason</env:Text>
                </env:Reason>
                <env:Detail>
                    <key1>value1</key1>
                    <key2>value2</key2>
                </env:Detail>
            </env:Fault>
        </env:Body>
    </env:Envelope>',
    'expectedResultEmptyArrayDetails' =>
    '<?xml version="1.0" encoding="utf-8" ?>
    <env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope">
        <env:Body>
            <env:Fault>
                <env:Code>
                    <env:Value>env:Sender</env:Value>
                </env:Code>
                <env:Reason>
                    <env:Text xml:lang="en">Fault reason</env:Text>
                </env:Reason>
                <env:Detail></env:Detail>
            </env:Fault>
        </env:Body>
    </env:Envelope>',
    'expectedResultObjectDetails' =>
    '<?xml version="1.0" encoding="utf-8" ?>
    <env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope">
        <env:Body>
            <env:Fault>
                <env:Code>
                    <env:Value>env:Sender</env:Value>
                </env:Code>
                <env:Reason>
                    <env:Text xml:lang="en">Fault reason</env:Text>
                </env:Reason>
            </env:Fault>
        </env:Body>
    </env:Envelope>',
    'expectedResultStringDetails' =>
    '<?xml version = "1.0" encoding = "utf-8" ?>
    <env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope">
        <env:Body>
            <env:Fault>
                <env:Code>
                    <env:Value>env:Sender</env:Value>
                </env:Code>
                <env:Reason>
                    <env:Text xml:lang="en">Fault reason</env:Text>
                </env:Reason>
                <env:Detail>String details</env:Detail>
            </env:Fault>
        </env:Body>
    </env:Envelope>',
    'expectedResultIndexArrayDetails' =>
    '<?xml version = "1.0" encoding = "utf-8" ?>
    <env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope">
        <env:Body>
            <env:Fault>
                <env:Code>
                    <env:Value>env:Sender</env:Value>
                </env:Code>
                <env:Reason>
                    <env:Text xml:lang="en">Fault reason</env:Text>
                </env:Reason>
                <env:Detail></env:Detail>
            </env:Fault>
        </env:Body>
    </env:Envelope>',
    'expectedResultComplexDataDetails' =>
    '<?xml version = "1.0" encoding = "utf-8" ?>
    <env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope">
        <env:Body>
            <env:Fault>
                <env:Code>
                    <env:Value>env:Sender</env:Value>
                </env:Code>
                <env:Reason>
                    <env:Text xml:lang="en">Fault reason</env:Text>
                </env:Reason>
                <env:Detail>
                    <key>
                        <sub_key>value</sub_key>
                    </key>
                </env:Detail>
            </env:Fault>
        </env:Body>
    </env:Envelope>'
);
