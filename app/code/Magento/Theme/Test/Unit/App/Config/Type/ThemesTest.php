<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\App\Config\Type;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Theme\App\Config\Type\Themes;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class ThemesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Themes
     */
    private $model;

    /**
     * @var ConfigSourceInterface|Mock
     */
    private $configSourceMock;

    protected function setUp()
    {
        $this->configSourceMock = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();

        $this->model = new Themes(
            $this->configSourceMock
        );
    }

    public function testGet()
    {
        $this->configSourceMock->expects($this->once())
            ->method('get')
            ->willReturn(
                [
                    'Magento/luma' => [
                        'parent_id' => 'Magento/blank',
                        'theme_path' => 'Magento/luma',
                        'theme_title' => 'Magento Luma',
                        'preview_image' => 'preview_image_587df6c4e073d.jpeg',
                        'is_featured' => '0',
                        'area' => 'frontend',
                        'type' => '0',
                        'code' => 'Magento/luma',
                    ],
                ]
            );

        $this->assertSame(
            [
                'Magento/luma' => [
                    'parent_id' => 'Magento/blank',
                    'theme_path' => 'Magento/luma',
                    'theme_title' => 'Magento Luma',
                    'preview_image' => 'preview_image_587df6c4e073d.jpeg',
                    'is_featured' => '0',
                    'area' => 'frontend',
                    'type' => '0',
                    'code' => 'Magento/luma',
                ],
            ],
            $this->model->get()
        );
    }
}
