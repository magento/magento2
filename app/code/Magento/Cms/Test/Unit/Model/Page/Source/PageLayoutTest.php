<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\Page\Source;

use Magento\Cms\Model\Page\Source\PageLayout;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;
use Magento\Framework\View\PageLayout\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PageLayoutTest extends TestCase
{
    /**
     * @var BuilderInterface|MockObject
     */
    protected $builderMock;

    /**
     * @var Config|MockObject
     */
    protected $pageLayoutConfigMock;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var PageLayout
     */
    protected $object;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->builderMock = $this->getMockBuilder(
            BuilderInterface::class
        )->disableOriginalConstructor()
            ->onlyMethods(['getPageLayoutsConfig'])
            ->getMock();
        $this->pageLayoutConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOptions'])
            ->getMock();

        $this->builderMock->expects($this->any())
            ->method('getPageLayoutsConfig')
            ->willReturn($this->pageLayoutConfigMock);

        $this->object = $this->objectManagerHelper->getObject($this->getSourceClassName(), [
            'pageLayoutBuilder' => $this->builderMock,
        ]);
    }

    /**
     * @return string
     */
    protected function getSourceClassName()
    {
        return PageLayout::class;
    }

    /**
     * @param array $options
     * @param array $expected
     * @return void
     * @dataProvider getOptionsDataProvider
     */
    public function testToOptionArray(array $options, array $expected)
    {
        $this->pageLayoutConfigMock->expects($this->once())
            ->method('getOptions')
            ->willReturn($options);

        $this->assertSame($expected, $this->object->toOptionArray());
    }

    /**
     * @return array
     */
    public static function getOptionsDataProvider()
    {
        return [
            [
                [],
                [],
            ],
            [
                ['testStatus' => 'testValue'],
                [['label' => 'testValue', 'value' => 'testStatus']],
            ],

        ];
    }
}
