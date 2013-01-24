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

class Mage_Core_Model_Theme_FilesTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test crud operations for theme files model using valid data
     */
    public function testCrud()
    {
        /** @var $themeModel Mage_Core_Model_Theme_Files */
        $filesModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme_Files');
        $filesData = $this->_getThemeFilesValidData();

        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $theme = $themeModel->getCollection()->getFirstItem();

        $filesData['theme_id'] = $theme->getId();
        $filesModel->setData($filesData);

        $crud = new Magento_Test_Entity($filesModel, array('file_name' => 'rename.css'));
        $crud->testCrud();
    }

    /**
     * Get theme files valid data
     *
     * @return array
     */
    protected function _getThemeFilesValidData()
    {
        return array(
            'file_name' => 'main.css',
            'file_type' => 'css',
            'content'   => 'content files',
            'order'     => 0,
        );
    }

}
