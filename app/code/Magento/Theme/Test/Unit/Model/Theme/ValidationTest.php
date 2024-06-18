<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Theme data validation
 */
namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\DataObject;
use Magento\Framework\View\Design\Theme\Validator;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
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
        /** @var DataObject $themeMock */
        $themeMock = new DataObject();
        $themeMock->setData($data);

        $validator = new Validator();

        $this->assertEquals($result, $validator->validate($themeMock));
        $this->assertEquals($messages, $validator->getErrorMessages());
    }

    /**
     * @return array
     */
    public static function dataProviderValidate()
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
