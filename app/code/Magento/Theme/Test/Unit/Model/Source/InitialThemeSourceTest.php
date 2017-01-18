<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Source;

use Magento\Framework\App\DeploymentConfig;
use Magento\Theme\Model\ResourceModel\Theme;
use Magento\Theme\Model\ResourceModel\ThemeFactory;
use Magento\Theme\Model\Source\InitialThemeSource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use Magento\Framework\DB\Select;

class InitialThemeSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InitialThemeSource
     */
    private $model;

    /**
     * @var DeploymentConfig|Mock
     */
    private $deploymentConfigMock;

    /**
     * @var ThemeFactory|Mock
     */
    private $themeFactoryMock;

    /**
     * @var Theme|Mock
     */
    private $themeMock;

    /**
     * @var AdapterInterface|Mock
     */
    private $connectionMock;

    /**
     * @var Select|Mock
     */
    private $selectMock;

    protected function setUp()
    {
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeFactoryMock = $this->getMockBuilder(ThemeFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeMock = $this->getMockBuilder(Theme::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();

        $this->themeMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->themeFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->themeMock);
        $this->connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $this->selectMock->expects($this->any())
            ->method('sort')
            ->willReturnSelf();

        $this->model = new InitialThemeSource(
            $this->deploymentConfigMock,
            $this->themeFactoryMock
        );
    }

    public function testGetNotDeployed()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(false);

        $this->assertSame([], $this->model->get());
    }

    public function testGet()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(true);
        $this->connectionMock->expects($this->once())
            ->method('fetchAssoc')
            ->willReturn(
                [
                    '1' => [
                        'theme_id' => '1',
                        'parent_id' => null,
                        'theme_path' => 'Magento/backend',
                        'theme_title' => 'Magento 2 backend',
                        'preview_image' => null,
                        'is_featured' => '0',
                        'area' => 'adminhtml',
                        'type' => '0',
                        'code' => 'Magento/backend',
                    ],
                    '2' => [
                        'theme_id' => '2',
                        'parent_id' => null,
                        'theme_path' => 'Magento/blank',
                        'theme_title' => 'Magento Blank',
                        'preview_image' => 'preview_image_587df6c4cc9c2.jpeg',
                        'is_featured' => '0',
                        'area' => 'frontend',
                        'type' => '0',
                        'code' => 'Magento/blank',
                    ],
                    '3' => [
                        'theme_id' => '3',
                        'parent_id' => '2',
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
                'Magento/backend' => [
                    'parent_id' => null,
                    'theme_path' => 'Magento/backend',
                    'theme_title' => 'Magento 2 backend',
                    'preview_image' => null,
                    'is_featured' => '0',
                    'area' => 'adminhtml',
                    'type' => '0',
                    'code' => 'Magento/backend',
                ],
                'Magento/blank' => [
                    'parent_id' => null,
                    'theme_path' => 'Magento/blank',
                    'theme_title' => 'Magento Blank',
                    'preview_image' => 'preview_image_587df6c4cc9c2.jpeg',
                    'is_featured' => '0',
                    'area' => 'frontend',
                    'type' => '0',
                    'code' => 'Magento/blank',
                ],
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
