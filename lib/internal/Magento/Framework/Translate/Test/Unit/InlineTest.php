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
use Magento\Framework\Translate\Inline;
use Magento\Framework\Translate\Inline\ConfigInterface;
use Magento\Framework\Translate\Inline\ParserFactory;
use Magento\Framework\Translate\Inline\ParserInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InlineTest extends TestCase
{
    /**
     * @var ScopeResolverInterface|MockObject
     */
    protected $scopeResolverMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $configMock;

    /**
     * @var ParserFactory|MockObject
     */
    protected $parserMock;

    /**
     * @var StateInterface|MockObject
     */
    protected $stateMock;

    /**
     * @var AppState|MockObject
     */
    protected $appStateMock;

    protected function setUp(): void
    {
        $this->scopeResolverMock =
            $this->getMockForAbstractClass(ScopeResolverInterface::class);
        $this->urlMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->configMock = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->parserMock = $this->getMockForAbstractClass(ParserInterface::class);
        $this->stateMock = $this->getMockForAbstractClass(StateInterface::class);
        $this->appStateMock = $this->createMock(AppState::class);
    }

    /**
     * @param bool $isEnabled
     * @param bool $isActive
     * @param bool $isDevAllowed
     * @param string $area
     * @param bool $result
     * @dataProvider isAllowedDataProvider
     */
    public function testIsAllowed($isEnabled, $isActive, $isDevAllowed, $area, $result)
    {
        $this->prepareIsAllowed($isEnabled, $isActive, $isDevAllowed, null, $area);

        $model = new Inline(
            $this->scopeResolverMock,
            $this->urlMock,
            $this->layoutMock,
            $this->configMock,
            $this->parserMock,
            $this->stateMock,
            $this->appStateMock
        );

        $this->assertEquals($result, $model->isAllowed());
        $this->assertEquals($result, $model->isAllowed());
    }

    /**
     * @return array
     */
    public function isAllowedDataProvider()
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

    public function testGetParser()
    {
        $model = new Inline(
            $this->scopeResolverMock,
            $this->urlMock,
            $this->layoutMock,
            $this->configMock,
            $this->parserMock,
            $this->stateMock,
            $this->appStateMock
        );
        $this->assertEquals($this->parserMock, $model->getParser());
    }

    /**
     * @param string|array $body
     * @param string $expected
     * @dataProvider processResponseBodyStripInlineDataProvider
     */
    public function testProcessResponseBodyStripInline($body, $expected)
    {
        $scope = 'admin';
        $this->prepareIsAllowed(false, true, true, $scope);

        $model = new Inline(
            $this->scopeResolverMock,
            $this->urlMock,
            $this->layoutMock,
            $this->configMock,
            $this->parserMock,
            $this->stateMock,
            $this->appStateMock,
            '',
            '',
            $scope
        );
        $model->processResponseBody($body, true);
        $this->assertEquals($body, $expected);
    }

    /**
     * @return array
     */
    public function processResponseBodyStripInlineDataProvider()
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
     * @param string $scope
     * @param array|string $body
     * @param array|string $expected
     * @dataProvider processResponseBodyDataProvider
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testProcessResponseBody($scope, $body, $expected)
    {
        $isJson = true;
        $this->prepareIsAllowed(true, true, true, $scope);

        $jsonCall = is_array($body) ? 2 * (count($body) + 1) : 2;
        $this->parserMock->expects(
            $this->exactly($jsonCall)
        )->method(
            'setIsJson'
        )->willReturnMap(
            [
                [$isJson, $this->returnSelf()],
                [!$isJson, $this->returnSelf()],
            ]
        );
        $this->parserMock->expects(
            $this->exactly(1)
        )->method(
            'processResponseBodyString'
        )->with(
            is_array($body) ? reset($body) : $body
        );
        $this->parserMock->expects(
            $this->exactly(2)
        )->method(
            'getContent'
        )->willReturn(
            is_array($body) ? reset($body) : $body
        );

        $model = new Inline(
            $this->scopeResolverMock,
            $this->urlMock,
            $this->layoutMock,
            $this->configMock,
            $this->parserMock,
            $this->stateMock,
            $this->appStateMock,
            '',
            '',
            $scope
        );

        $model->processResponseBody($body, $isJson);
        $this->assertEquals($body, $expected);
    }

    /**
     * @return array
     */
    public function processResponseBodyDataProvider()
    {
        return [
            ['admin', 'test', 'test'],
            ['not_admin', 'test1', 'test1'],
        ];
    }

    /**
     * @param $scope
     * @param $body
     * @param $expected
     * @dataProvider processResponseBodyGetInlineScriptDataProvider
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testProcessResponseBodyGetInlineScript($scope, $body, $expected)
    {
        $isJson = true;
        $this->prepareIsAllowed(true, true, true, $scope);

        $jsonCall = is_array($body) ? 2 * (count($body) + 1) : 2;
        $this->parserMock->expects(
            $this->exactly($jsonCall)
        )->method(
            'setIsJson'
        )->willReturnMap(
            [
                [$isJson, $this->returnSelf()],
                [!$isJson, $this->returnSelf()],
            ]
        );
        $this->parserMock->expects(
            $this->exactly(1)
        )->method(
            'processResponseBodyString'
        )->with(
            is_array($body) ? reset($body) : $body
        );
        $this->parserMock->expects(
            $this->exactly(2)
        )->method(
            'getContent'
        )->willReturn(
            is_array($body) ? reset($body) : $body
        );

        $model = new Inline(
            $this->scopeResolverMock,
            $this->urlMock,
            $this->layoutMock,
            $this->configMock,
            $this->parserMock,
            $this->stateMock,
            $this->appStateMock,
            '',
            '',
            $scope
        );

        $model->processResponseBody($body, $isJson);
        $this->assertEquals($body, $expected);
    }

    /**
     * @return array
     */
    public function processResponseBodyGetInlineScriptDataProvider()
    {
        return [
            ['admin', 'test', 'test'],
            ['not_admin', 'test1', 'test1'],
        ];
    }

    /**
     * @param bool $isEnabled
     * @param bool $isActive
     * @param bool $isDevAllowed
     * @param null|string $scope
     */
    protected function prepareIsAllowed(
        $isEnabled,
        $isActive,
        $isDevAllowed,
        $scope = null,
        $area = Area::AREA_FRONTEND
    ) {
        $scopeMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->stateMock->expects($this->any())->method('isEnabled')->willReturn($isEnabled);
        $this->scopeResolverMock->expects(
            $this->once()
        )->method(
            'getScope'
        )->with(
            $scope
        )->willReturn(
            $scopeMock
        );

        $this->configMock->expects(
            $this->once()
        )->method(
            'isActive'
        )->with(
            $scopeMock
        )->willReturn(
            $isActive
        );

        $this->configMock->expects(
            $this->exactly((int)$isActive)
        )->method(
            'isDevAllowed'
        )->willReturn(
            $isDevAllowed
        );

        $this->appStateMock->expects(
            ($isActive && $isDevAllowed) ? $this->once() : $this->never()
        )->method(
            'getAreaCode'
        )->willReturn(
            $area
        );
    }
}
