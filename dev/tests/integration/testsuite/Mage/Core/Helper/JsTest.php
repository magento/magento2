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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Core
 */
class Mage_Core_Helper_JsTest extends PHPUnit_Framework_TestCase
{
    const FILE = 'blank.html';

    /**
     * @var Mage_Core_Helper_Js
     */
    protected $_helper;

    public function setUp()
    {
        $this->_helper = new Mage_Core_Helper_Js();
    }

    public function testGetTranslateJson()
    {
        $this->assertNotNull(json_decode($this->_helper->getTranslateJson()));
    }

    public function testGetTranslatorScript()
    {
        $this->assertEquals(
            '<script type="text/javascript">//<![CDATA['
                . "\nvar Translator = new Translate({$this->_helper->getTranslateJson()});\n"
                . '//]]></script>',
            $this->_helper->getTranslatorScript()
        );
    }

    public function testGetScript()
    {
        $this->assertEquals("<script type=\"text/javascript\">//<![CDATA[\ntest\n//]]></script>",
            $this->_helper->getScript('test')
        );
    }

    public function testIncludeScript()
    {
        $this->assertEquals('<script type="text/javascript" src="http://localhost/pub/js/blank.html"></script>' . "\n",
            $this->_helper->includeScript(self::FILE)
        );
        $script = '<script type="text/javascript" '
            . 'src="http://localhost/pub/media/skin/frontend/%s/%s/%s/%s/images/spacer.gif"></script>';
        $this->assertStringMatchesFormat($script, $this->_helper->includeScript('images/spacer.gif'));
    }
}
