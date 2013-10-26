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
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test crud operations for theme model using valid data
     *
     * @magentoDbIsolation enabled
     */
    public function testCrud()
    {
        /** @var $themeModel \Magento\View\Design\ThemeInterface */
        $themeModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\View\Design\ThemeInterface');
        $themeModel->setData($this->_getThemeValidData());

        $crud = new \Magento\TestFramework\Entity($themeModel, array('theme_version' => '2.0.0.1'));
        $crud->testCrud();
    }

    /**
     * Get theme valid data
     *
     * @return array
     */
    protected function _getThemeValidData()
    {
        return array(
            'area'                 => 'space_area',
            'theme_title'          => 'Space theme',
            'theme_version'        => '2.0.0.0',
            'parent_id'            => null,
            'is_featured'          => false,
            'theme_path'           => 'default/space',
            'preview_image'        => 'images/preview.png',
            'type'                 => \Magento\Core\Model\Theme::TYPE_VIRTUAL
        );
    }

    /**
     * Test theme on child relations
     */
    public function testChildRelation()
    {
        /** @var $theme \Magento\View\Design\ThemeInterface */
        /** @var $currentTheme \Magento\View\Design\ThemeInterface */
        $theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\View\Design\ThemeInterface');
        $collection = $theme->getCollection()->addTypeFilter(\Magento\Core\Model\Theme::TYPE_VIRTUAL);
        foreach ($collection as $currentTheme) {
            $parentTheme = $currentTheme->getParentTheme();
            if (!empty($parentTheme)) {
                $this->assertTrue($parentTheme->hasChildThemes());
            }
        }
    }
}
