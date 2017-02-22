<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Theme\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Collection
     */
    private $model;

    /**
     * @var \Magento\Framework\Config\ThemeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeConfigFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directory;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityFactory;

    /**
     * @var \Magento\Framework\View\Design\Theme\ThemePackageList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themePackageList;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readDirFactory;

    protected function setUp()
    {
        $this->entityFactory = $this->getMockBuilder('Magento\Framework\Data\Collection\EntityFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->themeConfigFactory = $this->getMockBuilder('Magento\Framework\Config\ThemeFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->directory = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->themePackageList = $this->getMock(
            '\Magento\Framework\View\Design\Theme\ThemePackageList',
            [],
            [],
            '',
            false
        );
        $this->readDirFactory = $this->getMock('Magento\Framework\Filesystem\Directory\ReadFactory', [], [], '', false);
        $this->readDirFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->directory));

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
        $themeConfig = $this->getMockBuilder('Magento\Framework\Config\Theme')->disableOriginalConstructor()->getMock();
        $theme = $this->getMockBuilder('Magento\Theme\Model\Theme')->disableOriginalConstructor()->getMock();
        $parentTheme = ['parentThemeCode'];
        $parentThemePath = 'frontend/parent/theme';

        $themePackage = $this->getMock('\Magento\Framework\View\Design\Theme\ThemePackage', [], [], '', false);
        $themePackage->expects($this->any())
            ->method('getArea')
            ->will($this->returnValue('frontend'));
        $themePackage->expects($this->any())
            ->method('getVendor')
            ->will($this->returnValue('theme'));
        $themePackage->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('code'));
        $this->themePackageList->expects($this->once())
            ->method('getThemes')
            ->will($this->returnValue([$themePackage]));
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
            ->with('Magento\Theme\Model\Theme')
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

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Constraint 'unsupported_type' is not supported
     */
    public function testAddConstraintUnsupportedType()
    {
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
    public function addConstraintDataProvider()
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
