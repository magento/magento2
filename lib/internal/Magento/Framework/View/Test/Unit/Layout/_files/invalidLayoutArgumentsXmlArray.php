<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'options without model attribute' => [
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <body>
                <block class="Magento\Test\Block" name="test.block">
                    <arguments>
                        <argument name="argumentName" xsi:type="options" />
                    </arguments>
                </block>
            </body>
        </page>',
        [
            "Element 'argument': The attribute 'model' is required but missing.\nLine: 5\nThe xml was: \n" .
            "0:<?xml version=\"1.0\"?>\n1:<page xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:            <body>\n3:                <block class=\"Magento\Test\Block\" name=\"test.block\">\n" .
            "4:                    <arguments>\n5:                        <argument name=\"argumentName\" " .
            "xsi:type=\"options\"/>\n6:                    </arguments>\n7:                </block>\n" .
            "8:            </body>\n9:        </page>\n"
        ],
    ],
    'url without path attribute' => [
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <body>
                <block class="Magento\Test\Block" name="test.block">
                    <arguments>
                        <argument name="argumentName" xsi:type="url" />
                    </arguments>
                </block>
            </body>
        </page>',
        [
            "Element 'argument': The attribute 'path' is required but missing.\nLine: 5\nThe xml was: \n" .
            "0:<?xml version=\"1.0\"?>\n1:<page xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:            <body>\n3:                <block class=\"Magento\Test\Block\" name=\"test.block\">\n" .
            "4:                    <arguments>\n5:                        <argument name=\"argumentName\" " .
            "xsi:type=\"url\"/>\n6:                    </arguments>\n7:                </block>\n" .
            "8:            </body>\n9:        </page>\n"
        ],
    ],
    'url without param name' => [
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <body>
                <block class="Magento\Test\Block" name="test.block">
                    <arguments>
                        <argument name="argumentName" xsi:type="url" path="module/controller/action">
                            <param />
                        </argument>
                    </arguments>
                </block>
            </body>
        </page>',
        [
            "Element 'param': The attribute 'name' is required but missing.\nLine: 6\nThe xml was: \n" .
            "1:<page xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n2:            <body>\n" .
            "3:                <block class=\"Magento\Test\Block\" name=\"test.block\">\n" .
            "4:                    <arguments>\n5:                        <argument name=\"argumentName\" " .
            "xsi:type=\"url\" path=\"module/controller/action\">\n6:                            <param/>\n" .
            "7:                        </argument>\n8:                    </arguments>\n9:                </block>\n" .
            "10:            </body>\n"
        ],
    ],
    'url with forbidden param attribute' => [
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <body>
            <block class="Magento\Test\Block" name="test.block">
                    <arguments>
                        <argument name="argumentName" xsi:type="url" path="module/controller/action">
                            <param name="paramName" forbidden="forbidden"/>
                        </argument>
                    </arguments>
                </block>
            </body>
        </page>',
        [
            "Element 'param', attribute 'forbidden': The attribute 'forbidden' is not allowed.\n" .
            "Line: 6\nThe xml was: \n1:<page xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:            <body>\n3:            <block class=\"Magento\Test\Block\" name=\"test.block\">\n" .
            "4:                    <arguments>\n5:                        <argument name=\"argumentName\" " .
            "xsi:type=\"url\" path=\"module/controller/action\">\n6:                            <param " .
            "name=\"paramName\" forbidden=\"forbidden\"/>\n7:                        </argument>\n" .
            "8:                    </arguments>\n9:                </block>\n10:            </body>\n"
        ],
     ],
    'url with forbidden param sub-element' => [
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <body>
                <block class="Magento\Test\Block" name="test.block">
                    <arguments>
                        <argument name="argumentName" xsi:type="url" path="module/controller/action">
                            <param name="paramName"><forbidden /></param>
                        </argument>
                    </arguments>
                </block>
            </body>
        </page>',
        [
            "Element 'forbidden': This element is not expected.\nLine: 6\nThe xml was: \n" .
            "1:<page xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n2:            <body>\n" .
            "3:                <block class=\"Magento\Test\Block\" name=\"test.block\">\n" .
            "4:                    <arguments>\n5:                        <argument name=\"argumentName\" " .
            "xsi:type=\"url\" path=\"module/controller/action\">\n6:                            <param " .
            "name=\"paramName\"><forbidden/></param>\n7:                        </argument>\n" .
            "8:                    </arguments>\n9:                </block>\n10:            </body>\n"
        ],
     ],
    'helper without helper attribute' => [
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <body>
                <block class="Magento\Test\Block" name="test.block">
                    <arguments>
                        <argument name="argumentName" xsi:type="helper" />
                    </arguments>
                </block>
            </body>
        </page>',
        [
            "Element 'argument': The attribute 'helper' is required but missing.\nLine: 5\nThe xml was: \n" .
            "0:<?xml version=\"1.0\"?>\n1:<page xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:            <body>\n3:                <block class=\"Magento\Test\Block\" name=\"test.block\">\n" .
            "4:                    <arguments>\n5:                        <argument name=\"argumentName\" " .
            "xsi:type=\"helper\"/>\n6:                    </arguments>\n7:                </block>\n" .
            "8:            </body>\n9:        </page>\n"
        ],
     ],
    'helper without param name' => [
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <body>
                <block class="Magento\Test\Block" name="test.block">
                    <arguments>
                        <argument name="argumentName" xsi:type="helper"
                            helper="Magento\Framework\View\Layout\Argument\Handler\TestHelper::testMethod">
                            <param />
                        </argument>
                    </arguments>
                </block>
            </body>
        </page>',
        [
            "Element 'param': The attribute 'name' is required but missing.\nLine: 7\nThe xml was: \n" .
            "2:            <body>\n3:                <block class=\"Magento\Test\Block\" name=\"test.block\">\n" .
            "4:                    <arguments>\n5:                        <argument name=\"argumentName\" " .
            "xsi:type=\"helper\" helper=\"Magento\Framework\View\Layout\Argument\Handler\TestHelper::testMethod\">\n" .
            "6:                            <param/>\n7:                        </argument>\n" .
            "8:                    </arguments>\n9:                </block>\n10:            </body>\n" .
            "11:        </page>\n"
        ],
     ],
    'helper with forbidden param attribute' => [
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <body>
                <block class="Magento\Test\Block" name="test.block">
                    <arguments>
                        <argument name="argumentName" xsi:type="helper"
                            helper="Magento\Framework\View\Layout\Argument\Handler\TestHelper::testMethod">
                            <param name="paramName" forbidden="forbidden"/>
                        </argument>
                    </arguments>
                </block>
            </body>
        </page>',
        [
            "Element 'param', attribute 'forbidden': The attribute 'forbidden' is not allowed.\nLine: 7\n" .
            "The xml was: \n2:            <body>\n3:                <block class=\"Magento\Test\Block\" " .
            "name=\"test.block\">\n4:                    <arguments>\n5:                        <argument " .
            "name=\"argumentName\" xsi:type=\"helper\" " .
            "helper=\"Magento\Framework\View\Layout\Argument\Handler\TestHelper::testMethod\">\n" .
            "6:                            <param name=\"paramName\" forbidden=\"forbidden\"/>\n" .
            "7:                        </argument>\n8:                    </arguments>\n" .
            "9:                </block>\n10:            </body>\n11:        </page>\n"
        ],
     ],
    'helper with forbidden param sub-element' => [
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <body>
                <block class="Magento\Test\Block" name="test.block">
                    <arguments>
                        <argument name="argumentName" xsi:type="helper"
                            helper="Magento\Framework\View\Layout\Argument\Handler\TestHelper::testMethod">
                            <param name="paramName"><forbidden /></param>
                        </argument>
                    </arguments>
                </block>
            </body>
        </page>',
        [
            "Element 'forbidden': This element is not expected.\nLine: 7\nThe xml was: \n2:            <body>\n" .
            "3:                <block class=\"Magento\Test\Block\" name=\"test.block\">\n" .
            "4:                    <arguments>\n5:                        <argument name=\"argumentName\" " .
            "xsi:type=\"helper\" helper=\"Magento\Framework\View\Layout\Argument\Handler\TestHelper::testMethod\">\n" .
            "6:                            <param name=\"paramName\"><forbidden/></param>\n" .
            "7:                        </argument>\n8:                    </arguments>\n" .
            "9:                </block>\n10:            </body>\n11:        </page>\n"
        ],
     ],
    'action with doubled arguments' => [
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                <body>
                    <block class="Magento\Test\Block" name="test.block">
                        <action method="testAction">
                            <argument name="string" xsi:type="string">string1</argument>
                            <argument name="string" xsi:type="string">string2</argument>
                        </action>
                    </block>
                </body>
            </page>',
        [
            "Element 'argument': Duplicate key-sequence ['string'] in key identity-constraint " .
            "'blockActionArgumentName'.\nLine: 6\nThe xml was: \n" .
            "1:<page xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n2:                <body>\n" .
            "3:                    <block class=\"Magento\Test\Block\" name=\"test.block\">\n" .
            "4:                        <action method=\"testAction\">\n" .
            "5:                            <argument name=\"string\" xsi:type=\"string\">string1</argument>\n" .
            "6:                            <argument name=\"string\" xsi:type=\"string\">string2</argument>\n" .
            "7:                        </action>\n8:                    </block>\n9:                </body>\n" .
            "10:            </page>\n"
        ],
    ],
];
