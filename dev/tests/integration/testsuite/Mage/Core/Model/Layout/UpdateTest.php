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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Core
 */
class Mage_Core_Model_Layout_UpdateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Layout_Update
     */
    protected $_model;

    public static function setUpBeforeClass()
    {
        /* Point application to predefined layout fixtures */
        Mage::getConfig()->setOptions(array(
            'design_dir' => dirname(__DIR__) . '/_files/design',
        ));
        Mage::getDesign()->setDesignTheme('test/default/default');

        /* Disable loading and saving layout cache */
        Mage::app()->getCacheInstance()->banUse('layout');
    }

    protected function setUp()
    {
        $this->_model = new Mage_Core_Model_Layout_Update();
    }

    public function testGetFileLayoutUpdatesXmlFromTheme()
    {
        $this->_replaceConfigLayoutUpdates('
            <core module="Mage_Core">
                <file>layout.xml</file>
            </core>
        ');
        $expectedXmlStr = $this->_readLayoutFileContents(
            __DIR__ . '/../_files/design/frontend/test/default/Mage_Core/layout.xml'
        );
        $actualXml = $this->_model->getFileLayoutUpdatesXml('frontend', 'test', 'default');
        $this->assertXmlStringEqualsXmlString($expectedXmlStr, $actualXml->asNiceXml());
    }

    public function testGetFileLayoutUpdatesXmlFromModule()
    {
        $this->_replaceConfigLayoutUpdates('
            <page module="Mage_Page">
                <file>layout.xml</file>
            </page>
        ');
        $expectedXmlStr = $this->_readLayoutFileContents(
            __DIR__ . '/../../../../../../../../app/code/core/Mage/Page/view/frontend/layout.xml'
        );
        $actualXml = $this->_model->getFileLayoutUpdatesXml('frontend', 'test', 'default');
        $this->assertXmlStringEqualsXmlString($expectedXmlStr, $actualXml->asNiceXml());
    }

    /**
     * Replace configuration XML node <area>/layout/updates with the desired content
     *
     * @param string $replacementXmlStr
     * @param string $area
     */
    protected function _replaceConfigLayoutUpdates($replacementXmlStr, $area = 'frontend')
    {
        /* Erase existing layout updates */
        unset(Mage::app()->getConfig()->getNode("{$area}/layout")->updates);
        /* Setup layout updates fixture */
        Mage::app()->getConfig()->extend(new Varien_Simplexml_Config("
            <config>
                <{$area}>
                    <layout>
                        <updates>
                            {$replacementXmlStr}
                        </updates>
                    </layout>
                </{$area}>
            </config>
        "));
    }

    /**
     * Retrieve contents of the layout update file, preprocessed to be comparable with the merged layout data
     *
     * @param string $filename
     * @return string
     */
    protected function _readLayoutFileContents($filename)
    {
        /* Load & render XML to get rid of comments and replace root node name from <layout> to <layouts> */
        $xml = simplexml_load_file($filename, 'Varien_Simplexml_Element');
        $text = '';
        foreach ($xml->children() as $child) {
            $text .= $child->asNiceXml();
        }
        return '<layouts>' . $text . '</layouts>';
    }

    /**
     * @expectedException Exception
     * @dataProvider getFileLayoutUpdatesXmlExceptionDataProvider
     */
    public function testGetFileLayoutUpdatesXmlException($configFixture)
    {
        $this->_replaceConfigLayoutUpdates($configFixture);
        $this->_model->getFileLayoutUpdatesXml('frontend', 'test', 'default');
    }

    public function getFileLayoutUpdatesXmlExceptionDataProvider()
    {
        return array(
            'non-existing layout file' => array('
                <core module="Mage_Core">
                    <file>non_existing_layout.xml</file>
                </core>
            '),
            'module attribute absence' => array('
                <core>
                    <file>layout.xml</file>
                </core>
            '),
            'non-existing module'      => array('
                <core module="Non_ExistingModule">
                    <file>layout.xml</file>
                </core>
            '),
        );
    }

    /**
     * @magentoConfigFixture current_store advanced/modules_disable_output/Mage_Catalog true
     * @magentoConfigFixture current_store advanced/modules_disable_output/Mage_Page    true
     */
    public function testGetFileLayoutUpdatesXmlDisabledOutput()
    {
        $this->_replaceConfigLayoutUpdates('
            <catalog module="Mage_Catalog">
                <file>layout.xml</file>
            </catalog>
            <core module="Mage_Core">
                <file>layout.xml</file>
            </core>
            <page module="Mage_Page">
                <file>layout.xml</file>
            </page>
        ');
        $expectedXmlStr = $this->_readLayoutFileContents(
            __DIR__ . '/../_files/design/frontend/test/default/Mage_Core/layout.xml'
        );
        $actualXml = $this->_model->getFileLayoutUpdatesXml('frontend', 'test', 'default');
        $this->assertXmlStringEqualsXmlString($expectedXmlStr, $actualXml->asNiceXml());
    }
}
