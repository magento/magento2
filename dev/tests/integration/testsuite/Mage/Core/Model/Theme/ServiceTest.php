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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test theme service model
 */
class Mage_Core_Model_Theme_ServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Mage_Core_Model_Theme_Service::getThemes
     */
    public function testGetThemes()
    {
        /** @var $themeService Mage_Core_Model_Theme_Service */
        $themeService = Mage::getObjectManager()->create('Mage_Core_Model_Theme_Service');
        $collection = $themeService->getThemes(1, Mage_Core_Model_Resource_Theme_Collection::DEFAULT_PAGE_SIZE);

        $this->assertLessThanOrEqual(
            Mage_Core_Model_Resource_Theme_Collection::DEFAULT_PAGE_SIZE, $collection->count()
        );

        /** @var $theme Mage_Core_Model_Theme */
        foreach ($collection as $theme) {
            $this->assertEquals('frontend', $theme->getArea());
            $this->assertFalse($theme->isVirtual());
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @covers Mage_Core_Model_Theme_Service::assignThemeToStores
     */
    public function testAssignThemeToStores()
    {
        $originalCount = $this->_getThemeCollection()->count();

        /** @var $themeService Mage_Core_Model_Theme_Service */
        $themeService = Mage::getObjectManager()->create('Mage_Core_Model_Theme_Service');
        /** @var $physicalTheme Mage_Core_Model_Theme_Service */
        $physicalTheme = $themeService->getThemes(1, 1)->fetchItem();
        $this->assertTrue((bool)$physicalTheme->getId(), 'Physical theme is not loaded');

        $storeView = Mage::app()->getAnyStoreView()->getId();
        $themeService->assignThemeToStores($physicalTheme->getId(), array($storeView));
        $this->assertEquals($originalCount + 1, $this->_getThemeCollection()->count());

        $configItem = Mage::app()->getConfig()->getConfigDataModel()->getCollection()
            ->addFieldToSelect(array('value'))
            ->addFieldToFilter('scope', Mage_Core_Model_Config::SCOPE_STORES)
            ->addFieldToFilter('scope_id', $storeView)
            ->fetchItem();
        $themeId = $this->_getThemeCollection()->setOrder('theme_id', Varien_Data_Collection_Db::SORT_ORDER_ASC)
            ->getLastItem()->getId();

        $this->assertEquals($configItem->getValue(), $themeId);
    }

    /**
     * @return Mage_Core_Model_Resource_Theme_Collection
     */
    protected function _getThemeCollection()
    {
        return Mage::getObjectManager()->create('Mage_Core_Model_Resource_Theme_Collection');
    }
}
