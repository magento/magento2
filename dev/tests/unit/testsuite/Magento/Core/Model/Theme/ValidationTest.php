<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
        return [
            [
                [
                    'theme_code' => 'Magento/iphone',
                    'theme_title' => 'Iphone',
                    'theme_version' => '2.0.0',
                    'parent_theme' => ['default', 'default'],
                    'theme_path' => 'Magento/iphone',
                    'preview_image' => 'images/preview.png',
                ],
                true,
                [],
            ],
            [
                [
                    'theme_code' => 'iphone#theme!!!!',
                    'theme_title' => 'Iphone',
                    'theme_version' => 'last theme version',
                    'parent_theme' => ['default', 'default'],
                    'theme_path' => 'magento_iphone',
                    'preview_image' => 'images/preview.png',
                ],
                false,
                [
                    'theme_version' => ['Theme version has not compatible format.']
                ],
            ],
            [
                [
                    'theme_code' => 'iphone#theme!!!!',
                    'theme_title' => '',
                    'theme_version' => '',
                    'parent_theme' => ['default', 'default'],
                    'theme_path' => 'magento_iphone',
                    'preview_image' => 'images/preview.png',
                ],
                false,
                [
                    'theme_version' => ['Field can\'t be empty'],
                    'theme_title' => ['Field title can\'t be empty']
                ],
            ],
        ];
    }
}
