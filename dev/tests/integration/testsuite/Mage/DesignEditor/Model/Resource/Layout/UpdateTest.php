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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Model_Resource_Layout_UpdateTest extends PHPUnit_Framework_TestCase
{
    /*
     * Test theme id
     */
    protected $_themeId;

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_Design_Package
     */
    protected $_design;

    protected function setUp()
    {
        $this->_objectManager = Mage::getObjectManager();
        $this->_design = $this->_objectManager->get('Mage_Core_Model_Design_Package');

        $this->_themeId = $this->_design->getDesignTheme()->getThemeId();
        /** @var $theme Mage_Core_Model_Theme */
        $theme = $this->_objectManager->create('Mage_Core_Model_Theme');
        $theme->load('Test Theme', 'theme_title');
        $this->_design->getDesignTheme()->setThemeId($theme->getId());
    }

    protected function tearDown()
    {
        $this->_design->getDesignTheme()->setThemeId($this->_themeId);
    }

    /**
     * @magentoDataFixture Mage/Core/_files/layout_update.php
     */
    public function testFetchUpdatesByHandle()
    {
        /** @var $resourceLayoutUpdate Mage_DesignEditor_Model_Resource_Layout_Update */
        $resourceLayoutUpdate = $this->_objectManager->create('Mage_DesignEditor_Model_Resource_Layout_Update');
        $result = $resourceLayoutUpdate->fetchUpdatesByHandle('test_handle');
        $this->assertEquals('not_temporarytemporary', $result);
    }

    /**
     * @magentoDataFixture Mage/Core/_files/layout_update.php
     */
    public function testMakeTemporaryLayoutUpdatesPermanent()
    {
        /** @var $coreLayoutUpdate Mage_Core_Model_Resource_Layout_Update */
        $coreLayoutUpdate = $this->_objectManager->create('Mage_Core_Model_Resource_Layout_Update');
        $resultBefore = $coreLayoutUpdate->fetchUpdatesByHandle('test_handle');
        $this->assertEquals('not_temporary', $resultBefore);

        /** @var $vdeLayoutUpdate Mage_DesignEditor_Model_Resource_Layout_Update */
        $vdeLayoutUpdate = $this->_objectManager->create('Mage_DesignEditor_Model_Resource_Layout_Update');
        $vdeLayoutUpdate->makeTemporaryLayoutUpdatesPermanent($this->_design->getDesignTheme()->getThemeId(),
            array(Mage_Core_Model_App::ADMIN_STORE_ID)
        );

        $resultAfter = $coreLayoutUpdate->fetchUpdatesByHandle('test_handle');
        $this->assertEquals('not_temporarytemporary', $resultAfter);
    }
}
