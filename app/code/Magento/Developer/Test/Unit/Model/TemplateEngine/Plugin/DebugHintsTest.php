<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\TemplateEngine\Plugin;

use Magento\Developer\Model\TemplateEngine\Plugin\DebugHints;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class DebugHintsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DebugHints
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Developer\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $devHelperMock;

    /**
     * @var \Magento\Framework\View\TemplateEngineFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->devHelperMock = $this->getMock('Magento\Developer\Helper\Data', [], [], '', false);
        $this->subjectMock = $this->getMock('Magento\Framework\View\TemplateEngineFactory', [], [], '', false);

        $this->model = $this->objectManagerHelper->getObject(
            'Magento\Developer\Model\TemplateEngine\Plugin\DebugHints',
            [
                'objectManager' => $this->objectManagerMock,
                'scopeConfig' => $this->scopeConfigMock,
                'storeManager' => $this->storeManager,
                'devHelper' => $this->devHelperMock
            ]
        );
    }

    /**
     * @param bool $showBlockHints
     * @return void
     * @dataProvider afterCreateActiveDataProvider
     */
    public function testAfterCreateActive($showBlockHints)
    {
        $this->devHelperMock->expects($this->once())
            ->method('isDevAllowed')
            ->willReturn(true);
        $this->setupConfigFixture(true, $showBlockHints);
        $engine = $this->getMock('Magento\Framework\View\TemplateEngineInterface');
        $engineDecorated = $this->getMock('Magento\Framework\View\TemplateEngineInterface');
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                'Magento\Developer\Model\TemplateEngine\Decorator\DebugHints',
                $this->identicalTo(['subject' => $engine, 'showBlockHints' => $showBlockHints])
            )
            ->willReturn($engineDecorated);

        $this->assertEquals($engineDecorated, $this->model->afterCreate($this->subjectMock, $engine));
    }

    /**
     * @return array
     */
    public function afterCreateActiveDataProvider()
    {
        return ['block hints disabled' => [false], 'block hints enabled' => [true]];
    }

    /**
     * @param bool $isDevAllowed
     * @param bool $showTemplateHints
     * @return void
     * @dataProvider afterCreateInactiveDataProvider
     */
    public function testAfterCreateInactive($isDevAllowed, $showTemplateHints)
    {
        $this->devHelperMock->expects($this->any())
            ->method('isDevAllowed')
            ->willReturn($isDevAllowed);
        $this->setupConfigFixture($showTemplateHints, true);
        $this->objectManagerMock->expects($this->never())
            ->method('create');
        $engine = $this->getMock('Magento\Framework\View\TemplateEngineInterface');

        $this->assertSame($engine, $this->model->afterCreate($this->subjectMock, $engine));
    }

    /**
     * @return array
     */
    public function afterCreateInactiveDataProvider()
    {
        return [
            'dev disabled, template hints disabled' => [false, false],
            'dev disabled, template hints enabled' => [false, true],
            'dev enabled, template hints disabled' => [true, false]
        ];
    }

    /**
     * Setup fixture values for store config
     *
     * @param bool $showTemplateHints
     * @param bool $showBlockHints
     * @return void
     */
    protected function setupConfigFixture($showTemplateHints, $showBlockHints)
    {
        $storeCode = 'default';
        $storeMock = $this->getMock('Magento\Store\Api\Data\StoreInterface');
        $storeMock->expects($this->once())
            ->method('getCode')
            ->willReturn($storeCode);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturnMap([
                [
                    DebugHints::XML_PATH_DEBUG_TEMPLATE_HINTS,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeCode,
                    $showTemplateHints,
                ],
                [
                    DebugHints::XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeCode,
                    $showBlockHints
                ]
            ]);
    }
}
