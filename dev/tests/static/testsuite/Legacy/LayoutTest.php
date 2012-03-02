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
 * @category    tests
 * @package     static
 * @subpackage  Legacy
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Coverage of obsolete nodes in layout
 */
class Legacy_LayoutTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider layoutFileDataProvider
     */
    public function testLayoutFile($layoutFile)
    {
        $suggestion = sprintf(Legacy_ObsoleteCodeTest::SUGGESTION_MESSAGE, 'addCss/addJss');
        $layoutXml = simplexml_load_file($layoutFile);
        $selectorHeadBlock = '(name()="block" or name()="reference") and (@name="head" or @name="convert_root_head")';
        $this->assertEmpty(
            $layoutXml->xpath(
                '//*[' . $selectorHeadBlock . ']/action[@method="addItem"]'
            ),
            "Mage_Page_Block_Html_Head::addItem is obsolete. $suggestion"
        );
        $this->assertEmpty(
            $layoutXml->xpath(
                '//action[@method="addJs" or @method="addCss"]/parent::*[not(' . $selectorHeadBlock . ')]'
            ),
            "Calls addCss/addJs are allowed within the 'head' block only. Verify integrity of the nodes nesting."
        );
    }

    public function layoutFileDataProvider()
    {
        return Util_Files::getLayoutFiles();
    }
}
