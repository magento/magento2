<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test theme page layout config model
 */
namespace Magento\Theme\Test\Unit\Model\PageLayout\Config;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\PageLayout\Config;
use Magento\Framework\View\PageLayout\File\Collector\Aggregated;
use Magento\Theme\Model\PageLayout\Config\Builder;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use Magento\Theme\Model\Theme\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var \Magento\Framework\View\PageLayout\ConfigFactory|MockObject
     */
    protected $configFactory;

    /**
     * @var Aggregated|MockObject
     */
    protected $fileCollector;

    /**
     * @var Collection|MockObject
     */
    protected $themeCollection;

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->configFactory = $this->getMockBuilder(\Magento\Framework\View\PageLayout\ConfigFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->fileCollector = $this->getMockBuilder(
            Aggregated::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->themeCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeCollection->expects($this->once())
            ->method('setItemObjectClass')
            ->with(Data::class)
            ->willReturnSelf();

        $helper = new ObjectManager($this);
        $this->builder = $helper->getObject(
            Builder::class,
            [
                'configFactory' => $this->configFactory,
                'fileCollector' => $this->fileCollector,
                'themeCollection' => $this->themeCollection
            ]
        );
    }

    /**
     * Test get page layouts config
     *
     * @return void
     */
    public function testGetPageLayoutsConfig()
    {
        $files1 = ['content layouts_1.xml', 'content layouts_2.xml'];
        $files2 = ['content layouts_3.xml', 'content layouts_4.xml'];

        $theme1 = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $theme2 = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeCollection->expects($this->once())
            ->method('loadRegisteredThemes')
            ->willReturn([$theme1, $theme2]);

        $this->fileCollector->expects($this->exactly(2))
            ->method('getFilesContent')
            ->willReturnMap(
                [
                    [$theme1, 'layouts.xml', $files1],
                    [$theme2, 'layouts.xml', $files2]
                ]
            );

        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configFactory->expects($this->once())
            ->method('create')
            ->with(['configFiles' => array_merge($files1, $files2)])
            ->willReturn($config);

        $this->assertSame($config, $this->builder->getPageLayoutsConfig());
    }
}
