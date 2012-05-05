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
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Utility_LayoutTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Utility_Layout
     */
    protected $_utility;

    public static function setUpBeforeClass()
    {
        Mage::app()->getCacheInstance()->banUse('layout');
    }

    protected function setUp()
    {
        $this->_utility = new Mage_Core_Utility_Layout($this);
    }

    /**
     * Assert that the actual layout update instance represents the expected layout update file
     *
     * @param Mage_Core_Model_Layout_Update $actualUpdate
     * @param string $expectedUpdateFile
     */
    protected function _assertLayoutUpdate($actualUpdate, $expectedUpdateFile)
    {
        $this->assertInstanceOf('Mage_Core_Model_Layout_Update', $actualUpdate);

        $layoutUpdateXml = $actualUpdate->getFileLayoutUpdatesXml();
        $this->assertInstanceOf('Mage_Core_Model_Layout_Element', $layoutUpdateXml);
        $this->assertXmlStringEqualsXmlFile($expectedUpdateFile, $layoutUpdateXml->asNiceXml());
    }

    public function testGetLayoutUpdateFromFixture()
    {
        $layoutUpdateFile = __DIR__ . '/_files/_layout_update.xml';
        $layoutUpdate = $this->_utility->getLayoutUpdateFromFixture($layoutUpdateFile);
        $this->_assertLayoutUpdate($layoutUpdate, $layoutUpdateFile);
    }

    public function testGetLayoutFromFixture()
    {
        $layoutUpdateFile = __DIR__ . '/_files/_layout_update.xml';
        $layout = $this->_utility->getLayoutFromFixture($layoutUpdateFile);
        $this->assertInstanceOf('Mage_Core_Model_Layout', $layout);
        $this->_assertLayoutUpdate($layout->getUpdate(), $layoutUpdateFile);
    }
}
