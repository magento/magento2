<?php
/**
 * The list of all expected soap fault XMLs.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'expectedResultArrayDataDetails' => '<?xml version="1.0" encoding="utf-8" ?>
    <env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" xmlns:m="{wsdl_url}">
        <env:Body>
            <env:Fault>
                <env:Code>
                    <env:Value>env:Sender</env:Value>
                </env:Code>
                <env:Reason>
                    <env:Text xml:lang="en">Fault reason</env:Text>
                </env:Reason>
                <env:Detail>
                    <m:GenericFault>
                        <m:Parameters>
                            <m:GenericFaultParameter>
                                <m:key>key1</m:key>
                                <m:value>value1</m:value>
                            </m:GenericFaultParameter>
                            <m:GenericFaultParameter>
                                <m:key>key2</m:key>
                                <m:value>value2</m:value>
                            </m:GenericFaultParameter>
                            <m:GenericFaultParameter>
                                <m:key>1</m:key>
                                <m:value>value3</m:value>
                            </m:GenericFaultParameter>
                        </m:Parameters>
                        <m:Trace>Trace</m:Trace>
                    </m:GenericFault>
                </env:Detail>
            </env:Fault>
        </env:Body>
    </env:Envelope>',
    'expectedResultEmptyArrayDetails' => '<?xml version="1.0" encoding="utf-8" ?>
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
    'expectedResultObjectDetails' => '<?xml version="1.0" encoding="utf-8" ?>
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
    'expectedResultIndexArrayDetails' => '<?xml version = "1.0" encoding = "utf-8" ?>
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
    'expectedResultComplexDataDetails' => '<?xml version = "1.0" encoding = "utf-8" ?>
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
    </env:Envelope>'
];
