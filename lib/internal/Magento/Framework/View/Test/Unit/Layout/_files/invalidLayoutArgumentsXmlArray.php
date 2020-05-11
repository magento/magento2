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
        ["Element 'argument': The attribute 'model' is required but missing.\nLine: 5\n"], ],
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
        ["Element 'argument': The attribute 'path' is required but missing.\nLine: 5\n"], ],
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
        ["Element 'param': The attribute 'name' is required but missing.\nLine: 6\n"], ],
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
        ["Element 'param', attribute 'forbidden': The attribute 'forbidden' is not allowed.\nLine: 6\n"], ],
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
        ["Element 'forbidden': This element is not expected.\nLine: 6\n"], ],
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
        ["Element 'argument': The attribute 'helper' is required but missing.\nLine: 5\n"], ],
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
        ["Element 'param': The attribute 'name' is required but missing.\nLine: 7\n"], ],
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
        ["Element 'param', attribute 'forbidden': The attribute 'forbidden' is not allowed.\nLine: 7\n"], ],
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
        ["Element 'forbidden': This element is not expected.\nLine: 7\n"], ],
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
            "Element 'argument': Duplicate key-sequence ['string'] in key identity-constraint"
                . " 'blockActionArgumentName'.\nLine: 6\n"
        ],
    ],
];
