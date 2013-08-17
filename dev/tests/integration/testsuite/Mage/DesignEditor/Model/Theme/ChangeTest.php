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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme change test
 */
class Mage_DesignEditor_Model_Theme_ChangeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test crud operations for change model using valid data
     *
     * @magentoDbIsolation enabled
     */
    public function testCrud()
    {
        /** @var $changeModel Mage_DesignEditor_Model_Theme_Change */
        $changeModel = Mage::getObjectManager()->create('Mage_DesignEditor_Model_Theme_Change');
        $changeModel->setData($this->_getChangeValidData());

        $crud = new Magento_Test_Entity($changeModel, array('change_time' => '2012-06-10 20:00:01'));
        $crud->testCrud();
    }

    /**
     * Get change valid data
     *
     * @return array
     */
    protected function _getChangeValidData()
    {
        /** @var $theme Mage_Core_Model_Theme */
        /** @var $themeModel Mage_Core_Model_Theme */
        $theme = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $themeModel = $theme->getCollection()->getFirstItem();

        return array(
            'theme_id' => $themeModel->getId(),
            'change_time' => '2013-04-10 23:34:19',
        );
    }
}
