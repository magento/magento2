<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\Page\Source;

use Magento\Cms\Model\Page\Source\Theme;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Design\Theme\Label\ListInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ThemeTest extends TestCase
{
    /**
     * @var ListInterface|MockObject
     */
    protected $listMock;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var Theme
     */
    protected $object;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->listMock = $this->getMockBuilder(ListInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLabels'])
            ->getMockForAbstractClass();

        $this->object = $this->objectManagerHelper->getObject($this->getClassName(), [
            'themeList' => $this->listMock,
        ]);
    }

    /**
     * @return string
     */
    protected function getClassName()
    {
        return Theme::class;
    }

    /**
     * @param array $options
     * @param array $expected
     * @return void
     * @dataProvider getOptionsDataProvider
     */
    public function testToOptionArray(array $options, array $expected)
    {
        $this->listMock->expects($this->once())
            ->method('getLabels')
            ->willReturn($options);

        $this->assertEquals($expected, $this->object->toOptionArray());
    }

    /**
     * @return array
     */
    public static function getOptionsDataProvider()
    {
        return [
            [
                [],
                [['label' => 'Default', 'value' => '']],
            ],
            [
                [['label' => 'testValue', 'value' => 'testStatus']],
                [['label' => 'Default', 'value' => ''], ['label' => 'testValue', 'value' => 'testStatus']],
            ],
        ];
    }
}
