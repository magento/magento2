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
    'options without model attribute' => array(
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <block class="Magento\Test\Block" name="test.block">
                <arguments>
                    <argument name="argumentName" xsi:type="options" />
                </arguments>
            </block>
        </page>',
        array("Element 'argument': The attribute 'model' is required but missing.")),
    'url without path attribute' => array(
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <block class="Magento\Test\Block" name="test.block">
                <arguments>
                    <argument name="argumentName" xsi:type="url" />
                </arguments>
            </block>
        </page>',
        array("Element 'argument': The attribute 'path' is required but missing.")),
    'url without param name' => array(
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <block class="Magento\Test\Block" name="test.block">
                <arguments>
                    <argument name="argumentName" xsi:type="url" path="module/controller/action">
                        <param />
                    </argument>
                </arguments>
            </block>
        </page>',
        array("Element 'param': The attribute 'name' is required but missing.")),
    'url with forbidden param attribute' => array(
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <block class="Magento\Test\Block" name="test.block">
                <arguments>
                    <argument name="argumentName" xsi:type="url" path="module/controller/action">
                        <param name="paramName" forbidden="forbidden"/>
                    </argument>
                </arguments>
            </block>
        </page>',
        array("Element 'param', attribute 'forbidden': The attribute 'forbidden' is not allowed.")),
    'url with forbidden param sub-element' => array(
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <block class="Magento\Test\Block" name="test.block">
                <arguments>
                    <argument name="argumentName" xsi:type="url" path="module/controller/action">
                        <param name="paramName"><forbidden /></param>
                    </argument>
                </arguments>
            </block>
        </page>',
        array("Element 'forbidden': This element is not expected.")),
    'helper without helper attribute' => array(
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <block class="Magento\Test\Block" name="test.block">
                <arguments>
                    <argument name="argumentName" xsi:type="helper" />
                </arguments>
            </block>
        </page>',
        array("Element 'argument': The attribute 'helper' is required but missing.")),
    'helper without param name' => array(
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <block class="Magento\Test\Block" name="test.block">
                <arguments>
                    <argument name="argumentName" xsi:type="helper"
                        helper="Magento\Core\Model\Layout\Argument\Handler\TestHelper::testMethod">
                        <param />
                    </argument>
                </arguments>
            </block>
        </page>',
        array("Element 'param': The attribute 'name' is required but missing.")),
    'helper with forbidden param attribute' => array(
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <block class="Magento\Test\Block" name="test.block">
                <arguments>
                    <argument name="argumentName" xsi:type="helper"
                        helper="Magento\Core\Model\Layout\Argument\Handler\TestHelper::testMethod">
                        <param name="paramName" forbidden="forbidden"/>
                    </argument>
                </arguments>
            </block>
        </page>',
        array("Element 'param', attribute 'forbidden': The attribute 'forbidden' is not allowed.")),
    'helper with forbidden param sub-element' => array(
        '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <block class="Magento\Test\Block" name="test.block">
                <arguments>
                    <argument name="argumentName" xsi:type="helper"
                        helper="Magento\Core\Model\Layout\Argument\Handler\TestHelper::testMethod">
                        <param name="paramName"><forbidden /></param>
                    </argument>
                </arguments>
            </block>
        </page>',
        array("Element 'forbidden': This element is not expected.")),
    'action with doubled arguments' => array(
            '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                <block class="Magento\Test\Block" name="test.block">
                    <action method="testAction">
                        <argument name="string" xsi:type="string">string1</argument>
                        <argument name="string" xsi:type="string">string2</argument>
                    </action>
                </block>
            </page>',
        array(
            "Element 'argument': Duplicate key-sequence ['string'] in key identity-constraint 'actionArgumentName'."
        )),
);
