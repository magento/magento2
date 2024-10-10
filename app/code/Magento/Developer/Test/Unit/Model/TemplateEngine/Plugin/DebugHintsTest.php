<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Test\Unit\Model\TemplateEngine\Plugin;

use Magento\Developer\Helper\Data;
use Magento\Developer\Model\TemplateEngine\Decorator\DebugHints as DebugHintsDecorator;
use Magento\Developer\Model\TemplateEngine\Decorator\DebugHintsFactory;
use Magento\Developer\Model\TemplateEngine\Plugin\DebugHints;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\TemplateEngineFactory;
use Magento\Framework\View\TemplateEngineInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DebugHintsTest extends TestCase
{
    private const STORE_CODE = 'default';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Data|MockObject
     */
    private $devHelperMock;

    /**
     * @var DebugHintsFactory|MockObject
     */
    private $debugHintsFactoryMock;

    /**
     * @var Http|MockObject
     */
    private $httpMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->devHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->debugHintsFactoryMock = $this->getMockBuilder(
            DebugHintsFactory::class
        )
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpMock = $this->createMock(Http::class);

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->once())
            ->method('getCode')
            ->willReturn(static::STORE_CODE);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @param string $debugHintsPath
     * @param bool $showBlockHints
     * @param bool $debugHintsWithParam
     * @param bool $debugHintsParameter
     *
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

        $engine = $this->getMockForAbstractClass(TemplateEngineInterface::class);

        $debugHintsDecoratorMock = $this->getMockBuilder(
            DebugHintsDecorator::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->debugHintsFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'subject' => $engine,
                'showBlockHints' => $showBlockHints,
            ])
            ->willReturn($debugHintsDecoratorMock);

        $subjectMock = $this->getMockBuilder(TemplateEngineFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $debugHints = $this->objectManager->getObject(
            DebugHints::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'storeManager' => $this->storeManagerMock,
                'devHelper' => $this->devHelperMock,
                'debugHintsFactory' => $this->debugHintsFactoryMock,
                'http' => $this->httpMock,
                'debugHintsPath' => $debugHintsPath,
                'debugHintsWithParam' => $debugHintsWithParam,
                'debugHintsParameter' => $debugHintsParameter
            ]
        );

        $this->assertEquals($debugHintsDecoratorMock, $debugHints->afterCreate($subjectMock, $engine));
    }

    /**
     * @return array
     */
    public static function afterCreateActiveDataProvider()
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
     * @param bool $debugHintsWithParam
     * @param bool $debugHintsParameter
     *
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
        $this->devHelperMock
            ->method('isDevAllowed')
            ->willReturn($isDevAllowed);

        $this->setupConfigFixture($debugHintsPath, $showTemplateHints, true);

        $engine = $this->getMockForAbstractClass(TemplateEngineInterface::class);

        $subjectMock = $this->getMockBuilder(TemplateEngineFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $debugHints = $this->objectManager->getObject(
            DebugHints::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'storeManager' => $this->storeManagerMock,
                'devHelper' => $this->devHelperMock,
                'debugHintsFactory' => $this->debugHintsFactoryMock,
                'http' => $this->httpMock,
                'debugHintsPath' => $debugHintsPath,
                'debugHintsWithParam' => $debugHintsWithParam,
                'debugHintsParameter' => $debugHintsParameter
            ]
        );

        $this->assertSame($engine, $debugHints->afterCreate($subjectMock, $engine));
    }

    /**
     * @return array
     */
    public static function afterCreateInactiveDataProvider()
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
     *
     * @return void
     */
    private function setupConfigFixture($debugHintsPath, $showTemplateHints, $showBlockHints): void
    {
        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturnMap([
                [
                    $debugHintsPath,
                    ScopeInterface::SCOPE_STORE,
                    static::STORE_CODE,
                    $showTemplateHints,
                ],
                [
                    DebugHints::XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS,
                    ScopeInterface::SCOPE_STORE,
                    static::STORE_CODE,
                    $showBlockHints
                ]
            ]);
    }
}
