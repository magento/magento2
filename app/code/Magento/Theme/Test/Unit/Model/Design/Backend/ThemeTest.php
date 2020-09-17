<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Design\Backend;

use Magento\Framework\App\Area;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Model\Design\Backend\Theme;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ThemeTest extends TestCase
{
    /**
     * @var Theme
     */
    protected $model;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var DesignInterface|MockObject
     */
    protected $designMock;

    /**
     * @var TypeListInterface|MockObject
     */
    protected $cacheTypeListMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $configMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->designMock = $this->getMockBuilder(DesignInterface::class)
            ->getMock();
        $this->cacheTypeListMock = $this->getMockBuilder(TypeListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->getMockBuilder(ManagerInterface::class)
            ->getMock());
        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            Theme::class,
            [
                'design' => $this->designMock,
                'context' => $this->contextMock,
                'cacheTypeList' => $this->cacheTypeListMock,
                'config' => $this->configMock,
            ]
        );
    }

    /**
     * @test
     * @return void
     * @covers \Magento\Theme\Model\Design\Backend\Theme::beforeSave
     * @covers \Magento\Theme\Model\Design\Backend\Theme::__construct
     */
    public function testBeforeSave()
    {
        $this->designMock->expects($this->once())
            ->method('setDesignTheme')
            ->with('some_value', Area::AREA_FRONTEND);
        $this->model->setValue('some_value');
        $this->assertInstanceOf(get_class($this->model), $this->model->beforeSave());
    }

    /**
     * @param int $callNumber
     * @param string $oldValue
     * @dataProvider afterSaveDataProvider
     */
    public function testAfterSave($callNumber, $oldValue)
    {
        $this->cacheTypeListMock->expects($this->exactly($callNumber))
            ->method('invalidate');
        $this->configMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        Theme::XML_PATH_INVALID_CACHES,
                        ScopeInterface::SCOPE_STORE,
                        null,
                        ['block_html' => 1, 'layout' => 1, 'translate' => 1]
                    ],
                    [
                        null,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        $oldValue
                    ],

                ]
            );
        $this->model->setValue('some_value');
        $this->assertInstanceOf(get_class($this->model), $this->model->afterSave());
    }

    /**
     * @param string|null $value
     * @param string $expectedResult
     * @return void
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($value, $expectedResult)
    {
        $this->model->setValue($value);
        $this->assertEquals($expectedResult, $this->model->getValue());
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        return [
            [null, ''],
            ['value', 'value']
        ];
    }

    /**
     * @return array
     */
    public function afterSaveDataProvider()
    {
        return [
            [0, 'some_value'],
            [2, 'other_value'],
        ];
    }

    public function testAfterDelete()
    {
        $this->configMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        Theme::XML_PATH_INVALID_CACHES,
                        ScopeInterface::SCOPE_STORE,
                        null,
                        ['block_html' => 1, 'layout' => 1, 'translate' => 1]
                    ],
                    [
                        null,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        'old_value'
                    ],

                ]
            );
        $this->cacheTypeListMock->expects($this->exactly(2))
            ->method('invalidate');
        $this->model->setValue('some_value');
        $this->assertSame($this->model, $this->model->afterDelete());
    }
}
