<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Model\Checkout;

use Magento\Captcha\Helper\Data;
use Magento\Captcha\Model\Checkout\ConfigProvider;
use Magento\Captcha\Model\DefaultModel;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
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
     * @var integer
     */
    protected $formId = 1;

    /**
     * @var ConfigProvider
     */
    protected $model;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->captchaHelperMock = $this->createMock(Data::class);
        $this->captchaMock = $this->createMock(DefaultModel::class);
        $this->storeMock = $this->createMock(Store::class);
        $formIds = [$this->formId];

        $this->model = new ConfigProvider(
            $this->storeManagerMock,
            $this->captchaHelperMock,
            $formIds
        );
    }

    /**
     * @dataProvider getConfigDataProvider
     * @param bool $isRequired
     * @param integer $captchaGenerations
     * @param array $expectedConfig
     */
    public function testGetConfig($isRequired, $captchaGenerations, $expectedConfig)
    {
        $this->captchaHelperMock->expects($this->any())->method('getCaptcha')->with($this->formId)
            ->willReturn($this->captchaMock);

        $this->captchaMock->expects($this->any())->method('isCaseSensitive')->willReturn(1);
        $this->captchaMock->expects($this->any())->method('getHeight')->willReturn('12px');
        $this->captchaMock->expects($this->any())->method('isRequired')->willReturn($isRequired);

        $this->captchaMock->expects($this->exactly($captchaGenerations))->method('generate');
        $this->captchaMock->expects($this->exactly($captchaGenerations))->method('getImgSrc')
            ->willReturn('source');

        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('isCurrentlySecure')->willReturn(true);
        $this->storeMock->expects($this->once())->method('getUrl')->with('captcha/refresh', ['_secure' => true])
            ->willReturn('https://magento.com/captcha');

        $config = $this->model->getConfig();
        unset($config['captcha'][$this->formId]['timestamp']);
        $this->assertEquals($config, $expectedConfig);
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [
            [
                'isRequired' => true,
                'captchaGenerations' => 1,
                'expectedConfig' => [
                    'captcha' => [
                        $this->formId => [
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
                        $this->formId => [
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
