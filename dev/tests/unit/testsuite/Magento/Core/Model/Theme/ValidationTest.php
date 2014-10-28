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
 * Theme data validation
 */
namespace Magento\Core\Model\Theme;

class ValidationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $data
     * @param bool $result
     * @param array $messages
     *
     * @covers \Magento\Framework\View\Design\Theme\Validator::validate
     * @dataProvider dataProviderValidate
     */
    public function testValidate(array $data, $result, array $messages)
    {
        /** @var $themeMock \Magento\Framework\Object */
        $themeMock = new \Magento\Framework\Object();
        $themeMock->setData($data);

        $validator = new \Magento\Framework\View\Design\Theme\Validator();

        $this->assertEquals($result, $validator->validate($themeMock));
        $this->assertEquals($messages, $validator->getErrorMessages());
    }

    public function dataProviderValidate()
    {
        return array(
            array(
                array(
                    'theme_code' => 'Magento/iphone',
                    'theme_title' => 'Iphone',
                    'theme_version' => '2.0.0',
                    'parent_theme' => array('default', 'default'),
                    'theme_path' => 'Magento/iphone',
                    'preview_image' => 'images/preview.png'
                ),
                true,
                array(),
            ),
            array(
                array(
                    'theme_code' => 'iphone#theme!!!!',
                    'theme_title' => 'Iphone',
                    'theme_version' => 'last theme version',
                    'parent_theme' => array('default', 'default'),
                    'theme_path' => 'magento_iphone',
                    'preview_image' => 'images/preview.png'
                ),
                false,
                array(
                    'theme_version' => array('Theme version has not compatible format.')
                ),
            ),
            array(
                array(
                    'theme_code' => 'iphone#theme!!!!',
                    'theme_title' => '',
                    'theme_version' => '',
                    'parent_theme' => array('default', 'default'),
                    'theme_path' => 'magento_iphone',
                    'preview_image' => 'images/preview.png'
                ),
                false,
                array(
                    'theme_version' => array('Field can\'t be empty'),
                    'theme_title' => array('Field title can\'t be empty')
                ),
            ),
        );
    }

}
