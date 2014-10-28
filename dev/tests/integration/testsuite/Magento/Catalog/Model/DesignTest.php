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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Catalog\Model\Design.
 */
namespace Magento\Catalog\Model;

class DesignTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Design
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Design'
        );
    }

    /**
     * @dataProvider getThemeModel
     */
    public function testApplyCustomDesign($theme)
    {
        $this->_model->applyCustomDesign($theme);
        $design = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        );
        $this->assertEquals('package', $design->getDesignTheme()->getPackageCode());
        $this->assertEquals('theme', $design->getDesignTheme()->getThemeCode());
    }

    /**
     * @return \Magento\Core\Model\Theme
     */
    public function getThemeModel()
    {
        $theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Design\ThemeInterface'
        );
        $theme->setData($this->_getThemeData());
        return array(array($theme));
    }

    /**
     * @return array
     */
    protected function _getThemeData()
    {
        return array(
            'theme_title' => 'Magento Theme',
            'theme_code' => 'theme',
            'package_code' => 'package',
            'theme_path' => 'package/theme',
            'theme_version' => '0.1.0',
            'parent_theme' => null,
            'is_featured' => true,
            'preview_image' => '',
            'theme_directory' => __DIR__ . '_files/design/frontend/default/default'
        );
    }
}
