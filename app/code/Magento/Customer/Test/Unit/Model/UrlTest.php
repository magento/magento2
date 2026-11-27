<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\Decoder;
use Magento\Framework\Url\Encoder;
use Magento\Framework\Url\HostChecker;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Unit test for \Magento\Customer\Model\Url
 */
class UrlTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var Session|MockObject
     */
    protected $customerSessionMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var HostChecker|MockObject
     */
    private $hostCheckerMock;

    /**
     * @var Url
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->customerSessionMock = $this->createPartialMockWithReflection(
            Session::class,
            [
                'getNoReferer'
            ]
        );
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->urlBuilderMock->method('sessionUrlVar')->willReturnArgument(0);

        $this->hostCheckerMock = $this->createMock(HostChecker::class);
        $this->hostCheckerMock->method('isOwnOrigin')
            ->willReturnMap([['https://example.com/', true]]);

        $this->model = $this->objectManager->getObject(
            Url::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'request' => $this->requestMock,
                'customerSession' => $this->customerSessionMock,
                'urlBuilder' => $this->urlBuilderMock,
                'urlEncoder' => new Encoder(),
                'urlDecoder' => new Decoder($this->urlBuilderMock),
                'hostChecker' => $this->hostCheckerMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetLoginUrlParamsForNoRouteReferrer()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with(Url::REFERER_QUERY_PARAM_NAME)
            ->willReturn(null);
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->willReturn(false);
        $this->customerSessionMock->expects($this->any())
            ->method('getNoReferer')
            ->willReturn(false);
        $this->requestMock->expects($this->any())
            ->method('isGet')
            ->willReturn(true);
        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturn('cms/noroute/index');

        $this->assertEquals([], $this->model->getLoginUrlParams());
    }

    /**
     * @param string $suffix
     * @param bool $accepted
     * @return void
     */
    #[DataProvider('refererProvider')]
    public function testLoginUrlsAcceptOnlyWellFormedReferer(string $suffix, bool $accepted): void
    {
        $referer = (new Encoder())->encode('https://example.com/') . $suffix;
        $this->requestMock->method('getParam')
            ->with(Url::REFERER_QUERY_PARAM_NAME)
            ->willReturn($referer);
        $this->scopeConfigMock->method('isSetFlag')->willReturn(true);
        $expectedParams = $accepted ? [Url::REFERER_QUERY_PARAM_NAME => $referer] : [];
        $this->urlBuilderMock->expects($this->exactly(2))
            ->method('getUrl')
            ->willReturnCallback(
                function (string $route, array $params) use ($expectedParams): string {
                    $this->assertSame($expectedParams, $params);
                    return $route;
                }
            );

        $this->model->getLoginUrl();
        $this->model->getLoginPostUrl();
    }

    /**
     * @return array
     */
    public static function refererProvider(): array
    {
        return [
            'valid encoded own url' => ['', true],
            'appended invalid utf-8 bytes' => ["\xC0\xA7", false],
        ];
    }
}
