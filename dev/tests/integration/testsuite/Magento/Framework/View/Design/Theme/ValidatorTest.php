<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
            \Magento\Framework\View\Design\Theme\Validator::class
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
            \Magento\Framework\View\Design\Theme\Validator::class
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
            \Magento\Framework\View\Design\ThemeInterface::class
        );
    }

    /**
     * Get theme valid data
     *
     * @return array
     */
    protected function _getThemeValidData()
    {
        return [
            'theme_code' => 'space',
            'theme_title' => 'Space theme',
            'parent_theme' => null,
            'theme_path' => 'default/space',
            'preview_image' => 'images/preview.png'
        ];
    }

    /**
     * Get theme invalid data
     *
     * @return array
     */
    protected function _getThemeInvalidData()
    {
        return [
            'theme_code' => 'space',
            'theme_title' => '',
            'parent_theme' => null,
            'theme_path' => 'default/space',
            'preview_image' => 'images/preview.png'
        ];
    }
}
