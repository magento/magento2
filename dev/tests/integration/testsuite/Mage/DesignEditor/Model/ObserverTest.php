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
 * @package     Mage_DesignEditor
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test for skin changing observer
 */
class Mage_DesignEditor_Model_ObserverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_DesignEditor_Model_Observer
     */
    protected $_observer;

    /**
     * @var Varien_Event_Observer
     */
    protected $_eventObserver;

    protected function setUp()
    {
        $this->_observer = new Mage_DesignEditor_Model_Observer;

        $this->_eventObserver = new Varien_Event_Observer();
        $this->_eventObserver->setEvent(new Varien_Event(array('layout' => Mage::app()->getLayout())));
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     * @magentoConfigFixture current_store admin/security/session_lifetime 100
     */
    public function testPreDispatchDeactivateDesignEditor()
    {
        /** @var $session Mage_DesignEditor_Model_Session */
        $session = Mage::getSingleton('Mage_DesignEditor_Model_Session');
        $this->assertNotEmpty($session->getData(Mage_DesignEditor_Model_Session::SESSION_DESIGN_EDITOR_ACTIVE));
        /* active admin session */
        $this->_observer->preDispatch($this->_eventObserver);
        $this->assertNotEmpty($session->getData(Mage_DesignEditor_Model_Session::SESSION_DESIGN_EDITOR_ACTIVE));
        /* expired admin session */
        $session->setUpdatedAt(time() - 101);
        $this->_observer->preDispatch($this->_eventObserver);
        $this->assertEmpty($session->getData(Mage_DesignEditor_Model_Session::SESSION_DESIGN_EDITOR_ACTIVE));
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     */
    public function testPreDispatchApplyDesign()
    {
        $newSkin = 'default/default/blank';
        $this->assertNotEquals($newSkin, Mage::getDesign()->getDesignTheme());
        Mage::getSingleton('Mage_DesignEditor_Model_Session')->setSkin($newSkin);
        $this->_observer->preDispatch($this->_eventObserver);
        $this->assertEquals($newSkin, Mage::getDesign()->getDesignTheme());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     */
    public function testPreDispatchApplyDesignIgnoreNoSkin()
    {
        $currentSkin = Mage::getDesign()->getDesignTheme();
        $this->assertEmpty(Mage::getSingleton('Mage_DesignEditor_Model_Session')->getSkin());
        $this->_observer->preDispatch($this->_eventObserver);
        $this->assertEquals($currentSkin, Mage::getDesign()->getDesignTheme());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testPreDispatchApplyDesignInactive()
    {
        $newSkin = 'default/default/blank';
        $oldSkin = Mage::getDesign()->getDesignTheme();
        $this->assertNotEquals($newSkin, $oldSkin);
        Mage::getSingleton('Mage_DesignEditor_Model_Session')->setSkin($newSkin);
        $this->_observer->preDispatch($this->_eventObserver);
        $this->assertEquals($oldSkin, Mage::getDesign()->getDesignTheme());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     */
    public function testAddToolbar()
    {
        $layoutUpdate = Mage::app()->getLayout()->getUpdate();
        $this->assertNotContains(Mage_DesignEditor_Model_Observer::TOOLBAR_HANDLE, $layoutUpdate->getHandles());
        $this->_observer->addToolbar($this->_eventObserver);
        $this->assertContains(Mage_DesignEditor_Model_Observer::TOOLBAR_HANDLE, $layoutUpdate->getHandles());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testDisableBlocksOutputCachingInactive()
    {
        Mage::app()->getCacheInstance()->allowUse(Mage_Core_Block_Abstract::CACHE_GROUP);
        $this->_observer->disableBlocksOutputCaching(new Varien_Event_Observer());
        $this->assertTrue(Mage::app()->useCache(Mage_Core_Block_Abstract::CACHE_GROUP));
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     */
    public function testDisableBlocksOutputCachingActive()
    {
        Mage::app()->getCacheInstance()->allowUse(Mage_Core_Block_Abstract::CACHE_GROUP);
        $this->_observer->disableBlocksOutputCaching(new Varien_Event_Observer());
        $this->assertFalse(Mage::app()->useCache(Mage_Core_Block_Abstract::CACHE_GROUP));
    }

    /**
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     */
    public function testSetDesignEditorFlag()
    {
        $headBlock = new Mage_Page_Block_Html_Head();
        $layout = new Mage_Core_Model_Layout();
        $layout->addBlock($headBlock, 'head');
        $this->assertEmpty($headBlock->getDesignEditorActive());
        $observerData = new Varien_Event_Observer(array('event' => new Varien_Object(array('layout' => $layout))));
        $this->_observer->setDesignEditorFlag($observerData);
        $this->assertNotEmpty($headBlock->getDesignEditorActive());
    }

    /**
     * @param string $elementName
     * @param string $elementHtml
     * @param string $expectedOutput
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     * @dataProvider wrapPageElementDataProvider
     */
    public function testWrapPageElement($elementName, $elementHtml, $expectedOutput)
    {
        $actualOutput = $this->_wrapElement($elementName, $elementHtml);
        $this->assertXmlStringEqualsXmlString("<root>$expectedOutput</root>", "<root>$actualOutput</root>");
    }

    /**
     * @return array
     */
    public function wrapPageElementDataProvider()
    {
        return array(
            'non-draggable block' => array(
                'non_draggable_block',
                '<b>Non-Draggable Block</b>',
                '<b>Non-Draggable Block</b>',
            ),
            'non-draggable VDE block' => array(
                'non_draggable_vde_block',
                '<b>Non-Draggable VDE Block</b>',
                '<b>Non-Draggable VDE Block</b>',
            ),
            'draggable block' => array(
                'draggable_block',
                '<b>Draggable Block</b>',
                '<div id="vde_element_ZHJhZ2dhYmxlX2Jsb2Nr" class="vde_element_wrapper vde_draggable">
                    <div class="vde_element_title">draggable_block</div>
                    <b>Draggable Block</b>
                </div>',
            ),
            'non-draggable container' => array(
                'non_draggable_container',
                '<b>Non-Draggable Container Text</b>',
                '<div id="vde_element_bm9uX2RyYWdnYWJsZV9jb250YWluZXI" class="vde_element_wrapper vde_container">
                    <div class="vde_element_title">Non-Draggable Container</div>
                    <b>Non-Draggable Container Text</b>
                </div>',
            ),
            'draggable container' => array(
                'draggable_container',
                '<b>Draggable Container Text</b>',
                '<div id="vde_element_ZHJhZ2dhYmxlX2NvbnRhaW5lcg"
                    class="vde_element_wrapper vde_draggable vde_container">
                    <div class="vde_element_title">Draggable Container</div>
                    <b>Draggable Container Text</b>
                </div>',
            ),
            'VDE toolbar injection' => array(
                'after_body_start',
                '<b>Page Top Container Text</b>',
                '<div>VDE Toolbar</div>
                <div id="vde_element_YWZ0ZXJfYm9keV9zdGFydA" class="vde_element_wrapper vde_container">
                    <div class="vde_element_title">Page Top</div>
                    <b>Page Top Container Text</b>
                </div>',
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     */
    public function testWrapPageElementHighlightingDisabled()
    {
        $elementName = 'draggable_block';
        $elementHtml = '<b>Draggable Block</b>';
        $expectedOutputFormat =
            '<div id="vde_element_ZHJhZ2dhYmxlX2Jsb2Nr" class="vde_element_wrapper vde_draggable vde_wrapper_hidden">%w'
                . '<div class="vde_element_title">draggable_block</div>%w'
            . '</div>%w'
            . '<!--start_vde_element_ZHJhZ2dhYmxlX2Jsb2Nr-->%w'
            . '<b>Draggable Block</b>%w'
            . '<!--end_vde_element_ZHJhZ2dhYmxlX2Jsb2Nr-->%w';

        Mage::getSingleton('Mage_Core_Model_Cookie')->set(Mage_DesignEditor_Model_Session::COOKIE_HIGHLIGHTING, 'off');
        $actualOutput = $this->_wrapElement($elementName, $elementHtml);
        $this->assertStringMatchesFormat($expectedOutputFormat, $actualOutput);
    }

    /**
     * Process wrapping of an element
     *
     * Generates layout and should be used with magentoAppIsolation enabled
     *
     * @param string $elementName
     * @param string $elementHtml
     * @return string
     */
    protected function _wrapElement($elementName, $elementHtml)
    {
        // create a layout object mock with fixture data
        $utility = new Mage_Core_Utility_Layout($this);
        $layoutMock = $utility->getLayoutFromFixture(
            __DIR__ . '/../_files/observer_test.xml', array(array('structure' => new Magento_Data_Structure))
        );

        // load the fixture data. This will populate layout structure as well
        $layoutMock->getUpdate()->addHandle('test_handle')->load();
        $layoutMock->generateXml()->generateElements();

        $transport = new Varien_Object(array('output' => $elementHtml));
        $observer = new Varien_Event_Observer(array(
            'event' => new Varien_Event(array(
                'layout' => $layoutMock,
                'element_name' => $elementName,
                'transport' => $transport,
            ))
        ));

        $this->_observer->wrapPageElement($observer);
        return $transport->getData('output');
    }

    /**
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     */
    public function testAdminSessionUserLogout()
    {
        /** @var $session Mage_DesignEditor_Model_Session */
        $session = Mage::getSingleton('Mage_DesignEditor_Model_Session');
        $this->assertTrue($session->isDesignEditorActive());
        $this->_observer->adminSessionUserLogout();
        $this->assertFalse($session->isDesignEditorActive());
    }
}
