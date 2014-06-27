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
 * Test theme data validator
 */
namespace Magento\Framework\View\Design\Theme;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test validator with valid data
     */
    public function testValidateWithValidData()
    {
        /** @var $validator \Magento\Framework\View\Design\Theme\Validator */
        $validator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Design\Theme\Validator'
        );

        $themeModel = $this->_getThemeModel();
        $themeModel->setData($this->_getThemeValidData());

        $this->assertEquals(true, $validator->validate($themeModel));
    }

    /**
     * Test validator with invalid data
     */
    public function testValidateWithInvalidData()
    {
        /** @var $validator \Magento\Framework\View\Design\Theme\Validator */
        $validator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Design\Theme\Validator'
        );

        $themeModel = $this->_getThemeModel();
        $themeModel->setData($this->_getThemeInvalidData());

        $this->assertEquals(false, $validator->validate($themeModel));
    }

    /**
     * Get theme model
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    protected function _getThemeModel()
    {
        return \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Design\ThemeInterface'
        );
    }

    /**
     * Get theme valid data
     *
     * @return array
     */
    protected function _getThemeValidData()
    {
        return array(
            'theme_code' => 'space',
            'theme_title' => 'Space theme',
            'theme_version' => '0.1.0',
            'parent_theme' => null,
            'theme_path' => 'default/space',
            'preview_image' => 'images/preview.png'
        );
    }

    /**
     * Get theme invalid data
     *
     * @return array
     */
    protected function _getThemeInvalidData()
    {
        return array(
            'theme_code' => 'space',
            'theme_title' => 'Space theme',
            'theme_version' => 'last version',
            'parent_theme' => null,
            'theme_path' => 'default/space',
            'preview_image' => 'images/preview.png'
        );
    }
}
