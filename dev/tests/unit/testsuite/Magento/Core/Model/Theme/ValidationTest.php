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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme data validation
 */
namespace Magento\Core\Model\Theme;

class ValidationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test validator with valid data
     *
     * @covers \Magento\View\Design\Theme\Validator::validate
     */
    public function testValidateWithValidData()
    {
        /** @var $themeMock \Magento\Object */
        $themeMock = new \Magento\Object();
        $themeMock->setData($this->_getThemeValidData());

        /** @var $validatorMock \Magento\View\Design\Theme\Validator */
        $validatorMock = $this->getMock(
            'Magento\View\Design\Theme\Validator',
            array('_setThemeValidators'),
            array(),
            '',
            false
        );

        $versionValidators = array(
            array(
                'name' => 'available',
                'class' => 'Zend_Validate_Regex',
                'break' => true,
                'options' => array('pattern' => '/([a-z0-9\_]+)/'),
                'message' => 'Theme code has not compatible format'
            )
        );

        $validatorMock->addDataValidators('theme_code', $versionValidators);
        $this->assertEquals(true, $validatorMock->validate($themeMock));
    }

    /**
     * Test validator with invalid data
     *
     * @covers \Magento\View\Design\Theme\Validator::validate
     */
    public function testValidateWithInvalidData()
    {
        /** @var $themeMock \Magento\Object */
        $themeMock = new \Magento\Object();
        $themeMock->setData($this->_getThemeInvalidData());

        /** @var $helper \Magento\Core\Helper\Data */
        $helper = $this->getMockBuilder('Magento\Core\Helper\Data')->disableOriginalConstructor()->getMock();

        /** @var $validatorMock \Magento\View\Design\Theme\Validator */
        $validatorMock = $this->getMock(
            'Magento\View\Design\Theme\Validator',
            array('_setThemeValidators'),
            array($helper),
            '',
            true
        );

        $codeValidators = array(
            array(
                'name' => 'available',
                'class' => 'Zend_Validate_Regex',
                'break' => true,
                'options' => array('pattern' => '/^[a-z]+$/'),
                'message' => 'Theme code has not compatible format'
            )
        );

        $versionValidators = array(
            array(
                'name' => 'available',
                'class' => 'Zend_Validate_Regex',
                'break' => true,
                'options' => array('pattern' => '/(\d+\.\d+\.\d+\.\d+(\-[a-zA-Z0-9]+)?)|\*/'),
                'message' => 'Theme version has not compatible format.'
            )
        );

        $validatorMock->addDataValidators(
            'theme_code',
            $codeValidators
        )->addDataValidators(
            'theme_version',
            $versionValidators
        );
        $this->assertEquals(false, $validatorMock->validate($themeMock));
        $this->assertEquals($this->_getErrorMessages(), $validatorMock->getErrorMessages());
    }

    /**
     * Get theme valid data
     *
     * @return array
     */
    protected function _getThemeValidData()
    {
        return array(
            'theme_code' => 'iphone',
            'theme_title' => 'Iphone',
            'theme_version' => '2.0.0.0',
            'parent_theme' => array('default', 'default'),
            'theme_path' => 'magento_iphone',
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
            'theme_code' => 'iphone#theme!!!!',
            'theme_title' => 'Iphone',
            'theme_version' => 'last theme version',
            'parent_theme' => array('default', 'default'),
            'theme_path' => 'magento_iphone',
            'preview_image' => 'images/preview.png'
        );
    }

    /**
     * Get error messages
     *
     * @return array
     */
    protected function _getErrorMessages()
    {
        return array(
            'theme_code' => array('Theme code has not compatible format'),
            'theme_version' => array('Theme version has not compatible format.')
        );
    }
}
