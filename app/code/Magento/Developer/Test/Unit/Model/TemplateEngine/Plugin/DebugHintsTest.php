<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\TemplateEngine\Plugin;

use Magento\Developer\Model\TemplateEngine\Decorator\DebugHintsFactory;
use Magento\Developer\Model\TemplateEngine\Plugin\DebugHints;
use Magento\Store\Model\ScopeInterface;

class DebugHintsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Developer\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $devHelperMock;

    /**
     * @var DebugHintsFactory | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $debugHintsFactory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->devHelperMock = $this->getMockBuilder(\Magento\Developer\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->debugHintsFactory = $this->getMockBuilder(
            \Magento\Developer\Model\TemplateEngine\Decorator\DebugHintsFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string $debugHintsPath
     * @param bool $showBlockHints
     * @return void
     * @dataProvider afterCreateActiveDataProvider
     */
    public function testAfterCreateActive(
        $debugHintsPath,
        $showBlockHints,
        $debugHintsWithParam,
        $debugHintsParameter
    ) {
        $this->devHelperMock->expects($this->once())
            ->method('isDevAllowed')
            ->willReturn(true);

        $this->setupConfigFixture($debugHintsPath, true, $showBlockHints);

        $engine = $this->createMock(\Magento\Framework\View\TemplateEngineInterface::class);

        $debugHintsDecorator = $this->getMockBuilder(
            \Magento\Developer\Model\TemplateEngine\Decorator\DebugHints::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->debugHintsFactory->expects($this->once())
            ->method('create')
            ->with([
                'subject' => $engine,
                'showBlockHints' => $showBlockHints,
            ])
            ->willReturn($debugHintsDecorator);

        $subjectMock = $this->getMockBuilder(\Magento\Framework\View\TemplateEngineFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpMock = $this->createMock(\Magento\Framework\App\Request\Http::class);

        $debugHints = new DebugHints(
            $this->scopeConfigMock,
            $this->storeManager,
            $this->devHelperMock,
            $this->debugHintsFactory,
            $debugHintsPath,
            $this->httpMock,
            $debugHintsWithParam,
            $debugHintsParameter
        );

        $this->assertEquals($debugHintsDecorator, $debugHints->afterCreate($subjectMock, $engine));
    }

    /**
     * @return array
     */
    public function afterCreateActiveDataProvider()
    {
        return [
            ['dev/debug/template_hints_storefront', false, false, null],
            ['dev/debug/template_hints_storefront', true, false, null],
            ['dev/debug/template_hints_admin', false, false, null],
            ['dev/debug/template_hints_admin', true, false, null],
        ];
    }

    /**
     * @param string $debugHintsPath
     * @param bool $isDevAllowed
     * @param bool $showTemplateHints
     * @return void
     * @dataProvider afterCreateInactiveDataProvider
     */
    public function testAfterCreateInactive(
        $debugHintsPath,
        $isDevAllowed,
        $showTemplateHints,
        $debugHintsWithParam,
        $debugHintsParameter
    ) {
        $this->devHelperMock->expects($this->any())
            ->method('isDevAllowed')
            ->willReturn($isDevAllowed);

        $this->setupConfigFixture($debugHintsPath, $showTemplateHints, true);

        $engine = $this->createMock(\Magento\Framework\View\TemplateEngineInterface::class);

        $subjectMock = $this->getMockBuilder(\Magento\Framework\View\TemplateEngineFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpMock = $this->createMock(\Magento\Framework\App\Request\Http::class);

        $debugHints = new DebugHints(
            $this->scopeConfigMock,
            $this->storeManager,
            $this->devHelperMock,
            $this->debugHintsFactory,
            $debugHintsPath,
            $this->httpMock,
            $debugHintsWithParam,
            $debugHintsParameter
        );

        $this->assertSame($engine, $debugHints->afterCreate($subjectMock, $engine));
    }

    /**
     * @return array
     */
    public function afterCreateInactiveDataProvider()
    {
        return [
            ['dev/debug/template_hints_storefront', false, false, false, null],
            ['dev/debug/template_hints_storefront', false, true, false, null],
            ['dev/debug/template_hints_storefront', true, false, false, null],
            ['dev/debug/template_hints_admin', false, false, false, null],
            ['dev/debug/template_hints_admin', false, true, false, null],
            ['dev/debug/template_hints_admin', true, false, false, null],
        ];
    }

    /**
     * Setup fixture values for store config
     *
     * @param string $debugHintsPath
     * @param bool $showTemplateHints
     * @param bool $showBlockHints
     * @return void
     */
    protected function setupConfigFixture($debugHintsPath, $showTemplateHints, $showBlockHints)
    {
        $storeCode = 'default';
        $storeMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
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
                    $debugHintsPath,
                    ScopeInterface::SCOPE_STORE,
                    $storeCode,
                    $showTemplateHints,
                ],
                [
                    DebugHints::XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS,
                    ScopeInterface::SCOPE_STORE,
                    $storeCode,
                    $showBlockHints
                ]
            ]);
    }
}
