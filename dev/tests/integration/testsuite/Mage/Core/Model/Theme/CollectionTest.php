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
 * Test for filesystem themes collection
 */
class Mage_Core_Model_Theme_CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test load themes collection from filesystem
     */
    public function testLoadThemesFromFileSystem()
    {
        $pathPattern = implode(DS, array(__DIR__, '..', '_files', 'design', 'frontend', 'default', '*', 'theme.xml'));

        /** @var $collection Mage_Core_Model_Theme_Collection */
        $collection = Mage::getModel('Mage_Core_Model_Theme_Collection');
        $collection->addTargetPattern($pathPattern);

        $this->assertEquals($collection->getItemsArray(), $this->_expectedThemeList());
    }

    /**
     * Expected theme list
     *
     * @return array
     */
    protected function _expectedThemeList()
    {
        return array(
            'default' => array(
                'theme_code'           => 'default',
                'theme_title'          => 'Default',
                'theme_version'        => '2.0.0.0',
                'parent_theme'         => null,
                'is_featured'          => true,
                'magento_version_from' => '2.0.0.0-dev1',
                'magento_version_to'   => '*',
                'theme_path'           => 'default/default',
                'preview_image'        => '',
                'theme_directory'      => implode(
                    DIRECTORY_SEPARATOR,
                    array(__DIR__, '..', '_files', 'design', 'frontend', 'default', 'default')
                )
            ),
            'default_iphone' => array(
                'theme_code'           => 'default_iphone',
                'theme_title'          => 'Iphone',
                'theme_version'        => '2.0.0.0',
                'parent_theme'         => array('default', 'default'),
                'is_featured'          => false,
                'magento_version_from' => '2.0.0.0-dev1',
                'magento_version_to'   => '*',
                'theme_path'           => 'default/default_iphone',
                'preview_image'        => 'images/preview.png',
                'theme_directory'      => implode(
                    DIRECTORY_SEPARATOR,
                    array(__DIR__, '..', '_files', 'design', 'frontend', 'default', 'default_iphone')
                )
            ),
        );
    }
}
