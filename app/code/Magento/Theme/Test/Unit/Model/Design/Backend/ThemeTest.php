<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Unit\Model\Design\Backend;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Model\Design\Backend\Theme;

class ThemeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Theme\Model\Design\Backend\Theme
     */
    protected $model;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $designMock;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheTypeListMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->designMock = $this->getMockBuilder(\Magento\Framework\View\DesignInterface::class)->getMock();
        $this->cacheTypeListMock = $this->getMockBuilder(\Magento\Framework\App\Cache\TypeListInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)->getMock());
        $this->configMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Theme\Model\Design\Backend\Theme::class,
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
