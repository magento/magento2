<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Page\Source;

use Magento\Cms\Model\Page\Source\PageLayout;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;
use Magento\Framework\View\PageLayout\Config;

class PageLayoutTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $builderMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
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
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->builderMock = $this->getMockBuilder(
            \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface::class
        )->disableOriginalConstructor()
            ->setMethods(['getPageLayoutsConfig'])
            ->getMock();
        $this->pageLayoutConfigMock = $this->getMockBuilder(\Magento\Framework\View\PageLayout\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptions'])
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
        return \Magento\Cms\Model\Page\Source\PageLayout::class;
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
    public function getOptionsDataProvider()
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
