<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
                    'theme_title' => '',
                    'parent_theme' => ['default', 'default'],
                    'theme_path' => 'magento_iphone',
                    'preview_image' => 'images/preview.png',
                ],
                false,
                [
                    'theme_title' => ['Field title can\'t be empty']
                ],
            ],
        ];
    }
}
