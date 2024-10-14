<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\View\Design\Theme\ThemePackage;
use Magento\Framework\View\Design\Theme\ThemePackageList;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\Theme\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    private $model;

    /**
     * @var \Magento\Framework\Config\ThemeFactory|MockObject
     */
    private $themeConfigFactory;

    /**
     * @var ReadInterface|MockObject
     */
    private $directory;

    /**
     * @var EntityFactory|MockObject
     */
    private $entityFactory;

    /**
     * @var ThemePackageList|MockObject
     */
    private $themePackageList;

    /**
     * @var ReadFactory|MockObject
     */
    private $readDirFactory;

    protected function setUp(): void
    {
        $this->entityFactory = $this->getMockBuilder(EntityFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->themeConfigFactory = $this->getMockBuilder(\Magento\Framework\Config\ThemeFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->directory = $this->getMockBuilder(ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->themePackageList = $this->createMock(ThemePackageList::class);
        $this->readDirFactory = $this->createMock(ReadFactory::class);
        $this->readDirFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->directory);

        $this->model = new Collection(
            $this->entityFactory,
            $this->themeConfigFactory,
            $this->themePackageList,
            $this->readDirFactory
        );
    }

    /**
     * @test
     * @return void
     */
    public function testLoadData()
    {
        $fileContent = 'content file';
        $media = ['preview_image' => 'preview.jpg'];
        $themeTitle = 'Theme title';
        $themeConfigFile = 'theme.xml';
        $themeConfig = $this->getMockBuilder(
            \Magento\Framework\Config\Theme::class
        )->disableOriginalConstructor()
            ->getMock();
        $theme = $this->getMockBuilder(Theme::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parentTheme = ['parentThemeCode'];
        $parentThemePath = 'frontend/parent/theme';

        $themePackage = $this->createMock(ThemePackage::class);
        $themePackage->expects($this->any())
            ->method('getArea')
            ->willReturn('frontend');
        $themePackage->expects($this->any())
            ->method('getVendor')
            ->willReturn('theme');
        $themePackage->expects($this->any())
            ->method('getName')
            ->willReturn('code');
        $this->themePackageList->expects($this->once())
            ->method('getThemes')
            ->willReturn([$themePackage]);
        $this->directory->expects($this->once())
            ->method('isExist')
            ->with($themeConfigFile)
            ->willReturn(true);
        $this->directory->expects($this->once())
            ->method('readFile')
            ->with($themeConfigFile)
            ->willReturn($fileContent);
        $this->themeConfigFactory->expects($this->once())
            ->method('create')
            ->with(['configContent' => $fileContent])
            ->willReturn($themeConfig);
        $this->entityFactory->expects($this->any())
            ->method('create')
            ->with(ThemeInterface::class)
            ->willReturn($theme);
        $themeConfig->expects($this->once())
            ->method('getMedia')
            ->willReturn($media);
        $themeConfig->expects($this->once())
            ->method('getParentTheme')
            ->willReturn($parentTheme);
        $themeConfig->expects($this->once())
            ->method('getThemeTitle')
            ->willReturn($themeTitle);
        $theme->expects($this->once())
            ->method('addData')
            ->with(
                [
                    'parent_id' => null,
                    'type' => ThemeInterface::TYPE_PHYSICAL,
                    'area' => 'frontend',
                    'theme_path' => 'theme/code',
                    'code' => 'theme/code',
                    'theme_title' => $themeTitle,
                    'preview_image' => $media['preview_image'],
                    'parent_theme_path' => 'theme/parentThemeCode'
                ]
            )
            ->willReturnSelf();
        $theme->expects($this->once())
            ->method('getData')
            ->with('parent_theme_path')
            ->willReturn($parentThemePath);
        $theme->expects($this->once())
            ->method('getArea')
            ->willReturn('frontend');

        $this->assertInstanceOf(get_class($this->model), $this->model->loadData());
    }

    public function testAddConstraintUnsupportedType()
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Constraint \'unsupported_type\' is not supported');
        $this->model->addConstraint('unsupported_type', 'value');
    }

    /**
     * @param array $inputValues
     * @param array $expected
     *
     * @dataProvider addConstraintDataProvider
     */
    public function testAddConstraint(array $inputValues, array $expected)
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        foreach ($inputValues as $data) {
            $type = $data[0];
            $value = $data[1];
            $this->model->addConstraint($type, $value);
        }
        $default = [
            Collection::CONSTRAINT_AREA => [],
            Collection::CONSTRAINT_VENDOR => [],
            Collection::CONSTRAINT_THEME_NAME => []
        ];
        $expected = array_merge($default, $expected);
        $this->assertAttributeSame($expected, 'constraints', $this->model);
    }

    /**
     * @return array
     */
    public static function addConstraintDataProvider()
    {
        return [
            'area' => [
                [[Collection::CONSTRAINT_AREA, 'area']],
                [Collection::CONSTRAINT_AREA => ['area']]
            ],
            'vendor' => [
                [[Collection::CONSTRAINT_VENDOR, 'Vendor']],
                [Collection::CONSTRAINT_VENDOR => ['Vendor']]
            ],
            'theme name' => [
                [[Collection::CONSTRAINT_THEME_NAME, 'theme_name']],
                [Collection::CONSTRAINT_THEME_NAME => ['theme_name']]
            ],
            'area, vendor and theme name' => [
                [
                    [Collection::CONSTRAINT_AREA, 'area_one'],
                    [Collection::CONSTRAINT_AREA, 'area_two'],
                    [Collection::CONSTRAINT_VENDOR, 'Vendor'],
                    [Collection::CONSTRAINT_VENDOR, 'Vendor'],
                    [Collection::CONSTRAINT_THEME_NAME, 'theme_name']
                ],
                [
                    Collection::CONSTRAINT_AREA => ['area_one', 'area_two'],
                    Collection::CONSTRAINT_VENDOR => ['Vendor'],
                    Collection::CONSTRAINT_THEME_NAME => ['theme_name']
                ]
            ],
        ];
    }
}
