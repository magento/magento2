<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Translate\Test\Unit;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\Inline;
use Magento\Framework\Translate\Inline\ConfigInterface;
use Magento\Framework\Translate\Inline\ParserFactory;
use Magento\Framework\Translate\Inline\ParserInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Framework\Translate\Inline.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Inline
     */
    private $model;

    /**
     * @var ScopeResolverInterface|MockObject
     */
    private $scopeResolverMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

    /**
     * @var LayoutInterface|MockObject
     */
    private $layoutMock;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var ParserFactory|MockObject
     */
    private $parserMock;

    /**
     * @var StateInterface|MockObject
     */
    private $stateMock;

    /**
     * @var AppState|MockObject
     */
    private $appStateMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->scopeResolverMock =
            $this->getMockForAbstractClass(ScopeResolverInterface::class);
        $this->urlMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->configMock = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->parserMock = $this->getMockForAbstractClass(ParserInterface::class);
        $this->stateMock = $this->getMockForAbstractClass(StateInterface::class);
        $this->appStateMock = $this->createMock(AppState::class);
        $this->model = $this->objectManager->getObject(
            Inline::class,
            [
                'scopeResolver' => $this->scopeResolverMock,
                'url' => $this->urlMock,
                'layout' => $this->layoutMock,
                'config' => $this->configMock,
                'parser' => $this->parserMock,
                'state' => $this->stateMock,
                'appState' => $this->appStateMock,
            ]
        );
    }

    /**
     * Is allowed test
     *
     * @param bool $isEnabled
     * @param bool $isActive
     * @param bool $isDevAllowed
     * @param string $area
     * @param bool $result
     * @dataProvider isAllowedDataProvider
     */
    public function testIsAllowed(bool $isEnabled, bool $isActive, bool $isDevAllowed, string $area, bool $result): void
    {
        $this->prepareIsAllowed($isEnabled, $isActive, $isDevAllowed, null, $area);

        $this->assertEquals($result, $this->model->isAllowed());
        $this->assertEquals($result, $this->model->isAllowed());
    }

    /**
     * Data provider for testIsAllowed
     *
     * @return array
     */
    public static function isAllowedDataProvider(): array
    {
        return [
            [true, true, true, Area::AREA_FRONTEND, true],
            [true, false, true, Area::AREA_FRONTEND, false],
            [true, true, false, Area::AREA_FRONTEND, false],
            [true, false, false, Area::AREA_FRONTEND, false],
            [false, true, true, Area::AREA_FRONTEND, false],
            [false, false, true, Area::AREA_FRONTEND, false],
            [false, true, false, Area::AREA_FRONTEND, false],
            [false, false, false, Area::AREA_FRONTEND, false],
            [true, true, true, Area::AREA_GLOBAL, false],
            [true, true, true, Area::AREA_ADMINHTML, true],
            [true, true, true, Area::AREA_DOC, false],
            [true, true, true, Area::AREA_CRONTAB, false],
            [true, true, true, Area::AREA_WEBAPI_REST, false],
            [true, true, true, Area::AREA_WEBAPI_SOAP, false],
            [true, true, true, Area::AREA_GRAPHQL, false]
        ];
    }

    /**
     * Get parser test
     *
     * @return void
     */
    public function testGetParser(): void
    {
        $this->assertEquals($this->parserMock, $this->model->getParser());
    }

    /**
     * Process response body strip inline
     *
     * @param string|array $body
     * @param string|array $expected
     * @return void
     * @dataProvider processResponseBodyStripInlineDataProvider
     */
    public function testProcessResponseBodyStripInline($body, $expected): void
    {
        $scope = 'admin';
        $this->prepareIsAllowed(false, true, true, $scope);

        $model = $this->objectManager->getObject(
            Inline::class,
            [
                'scopeResolver' => $this->scopeResolverMock,
                'url' => $this->urlMock,
                'layout' => $this->layoutMock,
                'config' => $this->configMock,
                'parser' => $this->parserMock,
                'state' => $this->stateMock,
                'appState' => $this->appStateMock,
                'scope' => $scope,
            ]
        );
        $model->processResponseBody($body, true);
        $this->assertEquals($body, $expected);
    }

    /**
     * Data provider for testProcessResponseBodyStripInline
     *
     * @return array
     */
    public static function processResponseBodyStripInlineDataProvider(): array
    {
        return [
            ['test', 'test'],
            ['{{{aaaaaa}}{{bbbbb}}{{eeeee}}{{cccccc}}}', 'aaaaaa'],
            [['test1', 'test2'], ['test1', 'test2']],
            [['{{{aaaaaa}}', 'test3'], ['{{{aaaaaa}}', 'test3']],
            [['{{{aaaaaa}}{{bbbbb}}', 'test4'], ['{{{aaaaaa}}{{bbbbb}}', 'test4']],
            [['{{{aaaaaa}}{{bbbbb}}{{eeeee}}{{cccccc}}}', 'test5'], ['aaaaaa', 'test5']],
        ];
    }

    /**
     * Process response body
     *
     * @param string $scope
     * @param string $body
     * @param string $expected
     * @return void
     * @dataProvider processResponseBodyDataProvider
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testProcessResponseBody(string $scope, string $body, string $expected): void
    {
        $isJson = true;
        $this->prepareIsAllowed(true, true, true, $scope);

        $jsonCall = is_array($body) ? 2 * (count($body) + 1) : 2;
        $this->parserMock->expects($this->exactly($jsonCall))
            ->method('setIsJson')
            ->willReturnMap([[$isJson, $this->returnSelf()], [!$isJson, $this->returnSelf()]]);
        $this->parserMock->expects($this->once())
            ->method('processResponseBodyString')
            ->with(is_array($body) ? reset($body) : $body);
        $this->parserMock->expects($this->exactly(2))
            ->method('getContent')
            ->willReturn(is_array($body) ? reset($body) : $body);

        $model = $this->objectManager->getObject(
            Inline::class,
            [
                'scopeResolver' => $this->scopeResolverMock,
                'url' => $this->urlMock,
                'layout' => $this->layoutMock,
                'config' => $this->configMock,
                'parser' => $this->parserMock,
                'state' => $this->stateMock,
                'appState' => $this->appStateMock,
                'scope' => $scope,
            ]
        );

        $model->processResponseBody($body, $isJson);
        $this->assertEquals($body, $expected);
    }

    /**
     * Data provider for testProcessResponseBody
     *
     * @return array
     */
    public static function processResponseBodyDataProvider(): array
    {
        return [
            ['admin', 'test', 'test'],
            ['not_admin', 'test1', 'test1'],
        ];
    }

    /**
     * Process response body get script
     *
     * @param string $scope
     * @param string $body
     * @param string $expected
     * @return void
     * @dataProvider processResponseBodyGetInlineScriptDataProvider
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testProcessResponseBodyGetInlineScript(string $scope, string $body, string $expected): void
    {
        $isJson = true;
        $this->prepareIsAllowed(true, true, true, $scope);

        $jsonCall = is_array($body) ? 2 * (count($body) + 1) : 2;
        $this->parserMock->expects($this->exactly($jsonCall))
            ->method('setIsJson')
            ->willReturnMap([[$isJson, $this->returnSelf()], [!$isJson, $this->returnSelf()]]);
        $this->parserMock->expects($this->once())
            ->method('processResponseBodyString')
            ->with(is_array($body) ? reset($body) : $body);
        $this->parserMock->expects($this->exactly(2))
            ->method('getContent')
            ->willReturn(is_array($body) ? reset($body) : $body);

        $model = $this->objectManager->getObject(
            Inline::class,
            [
                'scopeResolver' => $this->scopeResolverMock,
                'url' => $this->urlMock,
                'layout' => $this->layoutMock,
                'config' => $this->configMock,
                'parser' => $this->parserMock,
                'state' => $this->stateMock,
                'appState' => $this->appStateMock,
                'scope' => $scope,
            ]
        );

        $model->processResponseBody($body, $isJson);
        $this->assertEquals($body, $expected);
    }

    /**
     * Data provider for testProcessResponseBodyGetInlineScript
     *
     * @return array
     */
    public static function processResponseBodyGetInlineScriptDataProvider(): array
    {
        return [
            ['admin', 'test', 'test'],
            ['not_admin', 'test1', 'test1'],
        ];
    }

    /**
     * Prepare is allowed
     *
     * @param bool $isEnabled
     * @param bool $isActive
     * @param bool $isDevAllowed
     * @param null|string $scope
     * @param string $area
     * @return void
     */
    protected function prepareIsAllowed(
        bool $isEnabled,
        bool $isActive,
        bool $isDevAllowed,
        ?string $scope = null,
        string $area = Area::AREA_FRONTEND
    ): void {
        $scopeMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->stateMock->expects($this->atLeastOnce())
            ->method('isEnabled')
            ->willReturn($isEnabled);
        $this->scopeResolverMock->expects($this->once())
            ->method('getScope')
            ->with($scope)
            ->willReturn($scopeMock);

        $this->configMock->expects($this->once())
            ->method('isActive')
            ->with($scopeMock)
            ->willReturn($isActive);

        $this->configMock->expects($this->exactly((int)$isActive))
            ->method('isDevAllowed')
            ->willReturn($isDevAllowed);

        $this->appStateMock->expects(($isActive && $isDevAllowed) ? $this->once() : $this->never())
            ->method('getAreaCode')
            ->willReturn($area);
    }
}
