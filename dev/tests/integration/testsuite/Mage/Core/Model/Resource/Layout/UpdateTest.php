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

class Mage_Core_Model_Resource_Layout_UpdateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Resource_Layout_Update
     */
    protected $_resourceModel;

    protected function setUp()
    {
        $this->_resourceModel = Mage::getModel('Mage_Core_Model_Resource_Layout_Update');
    }

    /**
     * @magentoDataFixture Mage/Core/_files/layout_update.php
     */
    public function testFetchUpdatesByHandle()
    {
        /** @var $theme Mage_Core_Model_Theme */
        $theme = Mage::getModel('Mage_Core_Model_Theme');
        $theme->load('Test Theme', 'theme_title');
        $result = $this->_resourceModel->fetchUpdatesByHandle('test_handle', $theme, Mage::app()->getStore());
        $this->assertEquals('not_temporary', $result);
    }

    /**
     * @magentoDataFixture Mage/Adminhtml/controllers/_files/cache/all_types_enabled.php
     * @magentoDataFixture Mage/Adminhtml/controllers/_files/cache/application_cache.php
     * @magentoDataFixture Mage/Core/_files/layout_cache.php
     */
    public function testSaveAfterClearCache()
    {
        /** @var $appCache Mage_Core_Model_Cache */
        $appCache = Mage::getSingleton('Mage_Core_Model_Cache');
        /** @var Mage_Core_Model_Cache_Type_Layout $layoutCache */
        $layoutCache = Mage::getSingleton('Mage_Core_Model_Cache_Type_Layout');

        $this->assertNotEmpty($appCache->load('APPLICATION_FIXTURE'));
        $this->assertNotEmpty($layoutCache->load('LAYOUT_CACHE_FIXTURE'));

        /** @var $layoutUpdate Mage_Core_Model_Layout_Update */
        $layoutUpdate = Mage::getModel('Mage_Core_Model_Layout_Update');
        $this->_resourceModel->save($layoutUpdate);

        $this->assertNotEmpty($appCache->load('APPLICATION_FIXTURE'), 'Non-layout cache must be kept');
        $this->assertFalse($layoutCache->load('LAYOUT_CACHE_FIXTURE'), 'Layout cache must be erased');
    }
}
