<?php
/**
 * Response redirector tests
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Store\Test\Unit\App\Response;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\App\Response\Redirect;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Store\App\Response\Redirect.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RedirectTest extends TestCase
{
    private const XML_PATH_USE_CUSTOM_ADMIN_URL = 'admin/url/use_custom';
    private const XML_PATH_CUSTOM_ADMIN_URL = 'admin/url/custom';

    private const STUB_INTERNAL_URL = 'http://internalurl.com/';
    private const STUB_EXTERNAL_URL = 'http://externalurl.com/';
    private const STUB_CUSTOM_ADMIN_URL = 'http://externalurl.com/admin/';

    /**
     * @var Redirect
     */
    private $model;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var State|MockObject
     */
    private $appStateMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->createMock(Http::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->appStateMock = $this->createMock(State::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $this->model = $objectManager->getObject(
            Redirect::class,
            [
                'request' => $this->requestMock,
                'storeManager' => $this->storeManagerMock,
                'appState' =>  $this->appStateMock,
                'scopeConfig' =>  $this->scopeConfigMock,
            ]
        );
    }

    /**
     * Success url test
     *
     * @dataProvider urlAddresses
     *
     * @param string $url
     * @param string $area
     * @param bool $isCustomAdminUrlEnabled
     * @param string $expectedUrl
     * @return void
     */
    public function testSuccessUrl(
        string $url,
        string $area,
        bool $isCustomAdminUrlEnabled,
        string $expectedUrl
    ): void {
        $testStoreMock = $this->createMock(Store::class);
        $testStoreMock->expects($this->atLeastOnce())
            ->method('getBaseUrl')
            ->willReturn(self::STUB_INTERNAL_URL);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn(null);
        $this->storeManagerMock->expects($this->atLeastOnce())
            ->method('getStore')
            ->willReturn($testStoreMock);
        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($area);
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(self::XML_PATH_USE_CUSTOM_ADMIN_URL, ScopeInterface::SCOPE_STORE)
            ->willReturn($isCustomAdminUrlEnabled);
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_CUSTOM_ADMIN_URL, ScopeInterface::SCOPE_STORE)
            ->willReturn(self::STUB_CUSTOM_ADMIN_URL);

        $this->assertEquals($expectedUrl, $this->model->success($url));
    }

    /**
     * Data provider for testSuccessUrlWithCustomAdminUrl
     *
     * @return array
     */
    public static function urlAddresses(): array
    {
        return [
            [self::STUB_CUSTOM_ADMIN_URL, Area::AREA_ADMINHTML, true, self::STUB_CUSTOM_ADMIN_URL],
            [self::STUB_CUSTOM_ADMIN_URL, Area::AREA_ADMINHTML, false, self::STUB_INTERNAL_URL],
            [self::STUB_CUSTOM_ADMIN_URL, Area::AREA_FRONTEND, true, self::STUB_INTERNAL_URL],
            [self::STUB_EXTERNAL_URL, Area::AREA_ADMINHTML, true, self::STUB_INTERNAL_URL],
            [self::STUB_EXTERNAL_URL, Area::AREA_FRONTEND, true, self::STUB_INTERNAL_URL],
        ];
    }
}
