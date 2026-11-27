<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Model\Checkout;

use Magento\Captcha\Helper\Data;
use Magento\Captcha\Model\Checkout\ConfigProvider;
use Magento\Captcha\Model\DefaultModel;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $captchaHelperMock;

    /**
     * @var MockObject
     */
    protected $captchaMock;

    /**
     * @var MockObject
     */
    protected $storeMock;

    /**
     * @var int
     */
    protected static $formId = 1;

    /**
     * @var ConfigProvider
     */
    protected $model;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->captchaHelperMock = $this->createMock(Data::class);
        $this->captchaMock = $this->createMock(DefaultModel::class);
        $this->storeMock = $this->createMock(Store::class);
        $formIds = [self::$formId];

        $this->model = new ConfigProvider(
            $this->storeManagerMock,
            $this->captchaHelperMock,
            $formIds
        );
    }

    /**
     * @param bool $isRequired
     * @param int $captchaGenerations
     * @param array $expectedConfig
     * @return void
     */
    #[DataProvider('getConfigDataProvider')]
    public function testGetConfig($isRequired, $captchaGenerations, $expectedConfig)
    {
        $this->captchaHelperMock->expects($this->any())->method('getCaptcha')->with(self::$formId)
            ->willReturn($this->captchaMock);

        $this->captchaMock->expects($this->any())->method('isCaseSensitive')->willReturn(1);
        $this->captchaMock->expects($this->any())->method('getHeight')->willReturn('12px');
        $this->captchaMock->expects($this->once())->method('isRequired')->willReturn($isRequired);

        $this->captchaMock->expects($this->exactly($captchaGenerations))->method('generate');
        $this->captchaMock->expects($this->exactly($captchaGenerations))->method('getImgSrc')
            ->willReturn('source');

        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('isCurrentlySecure')->willReturn(true);
        $this->storeMock->expects($this->once())->method('getUrl')->with('captcha/refresh', ['_secure' => true])
            ->willReturn('https://magento.com/captcha');

        $config = $this->model->getConfig();
        unset($config['captcha'][self::$formId]['timestamp']);
        $this->assertEquals($expectedConfig, $config);
    }

    /**
     * @return void
     */
    public function testGetConfigCachesRequirementsIndependentlyForEachForm(): void
    {
        $optionalCaptcha = $this->createMock(DefaultModel::class);
        $this->model = new ConfigProvider(
            $this->storeManagerMock,
            $this->captchaHelperMock,
            ['user_login', 'guest_checkout']
        );
        $this->captchaHelperMock->method('getCaptcha')->willReturnMap([
            ['user_login', $this->captchaMock],
            ['guest_checkout', $optionalCaptcha]
        ]);
        $this->captchaMock->expects($this->once())->method('isRequired')->willReturn(true);
        $optionalCaptcha->expects($this->once())->method('isRequired')->willReturn(false);
        $this->captchaMock->method('isCaseSensitive')->willReturn('1');
        $optionalCaptcha->method('isCaseSensitive')->willReturn('0');
        $this->captchaMock->method('getHeight')->willReturn(50);
        $optionalCaptcha->method('getHeight')->willReturn(30);
        $this->captchaMock->expects($this->exactly(2))->method('generate');
        $this->captchaMock->expects($this->exactly(2))->method('getImgSrc')->willReturn('login-image');
        $optionalCaptcha->expects($this->never())->method('generate');
        $optionalCaptcha->expects($this->never())->method('getImgSrc');
        $this->storeManagerMock->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->method('isCurrentlySecure')->willReturn(true);
        $this->storeMock->method('getUrl')->with('captcha/refresh', ['_secure' => true])
            ->willReturn('https://magento.com/captcha');

        for ($iteration = 0; $iteration < 2; $iteration++) {
            $config = $this->model->getConfig();
            $this->assertTrue($config['captcha']['user_login']['isRequired']);
            $this->assertFalse($config['captcha']['guest_checkout']['isRequired']);
            $this->assertSame('login-image', $config['captcha']['user_login']['imageSrc']);
            $this->assertSame('', $config['captcha']['guest_checkout']['imageSrc']);
            $this->assertTrue($config['captcha']['user_login']['isCaseSensitive']);
            $this->assertFalse($config['captcha']['guest_checkout']['isCaseSensitive']);
            $this->assertSame(50, $config['captcha']['user_login']['imageHeight']);
            $this->assertSame(30, $config['captcha']['guest_checkout']['imageHeight']);
            $this->assertSame('https://magento.com/captcha', $config['captcha']['user_login']['refreshUrl']);
            $this->assertSame('https://magento.com/captcha', $config['captcha']['guest_checkout']['refreshUrl']);
            $this->assertIsInt($config['captcha']['user_login']['timestamp']);
            $this->assertIsInt($config['captcha']['guest_checkout']['timestamp']);
        }
    }

    /**
     * @return array
     */
    public static function getConfigDataProvider()
    {
        return [
            [
                'isRequired' => true,
                'captchaGenerations' => 1,
                'expectedConfig' => [
                    'captcha' => [
                        self::$formId => [
                            'isCaseSensitive' => true,
                            'imageHeight' => '12px',
                            'imageSrc' => 'source',
                            'refreshUrl' => 'https://magento.com/captcha',
                            'isRequired' => true
                        ],
                    ],
                ],
            ],
            [
                'isRequired' => false,
                'captchaGenerations' => 0,
                'expectedConfig' => [
                    'captcha' => [
                        self::$formId => [
                            'isCaseSensitive' => true,
                            'imageHeight' => '12px',
                            'imageSrc' => '',
                            'refreshUrl' => 'https://magento.com/captcha',
                            'isRequired' => false
                        ],
                    ],
                ],
            ],
        ];
    }
}
