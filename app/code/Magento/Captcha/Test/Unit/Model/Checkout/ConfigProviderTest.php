<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Model\Checkout;

class ConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $captchaHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $captchaMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var integer
     */
    protected $formId = 1;

    /**
     * @var \Magento\Captcha\Model\Checkout\ConfigProvider
     */
    protected $model;

    protected function setUp()
    {
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->captchaHelperMock = $this->createMock(\Magento\Captcha\Helper\Data::class);
        $this->captchaMock = $this->createMock(\Magento\Captcha\Model\DefaultModel::class);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $formIds = [$this->formId];

        $this->model = new \Magento\Captcha\Model\Checkout\ConfigProvider(
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
            ->will($this->returnValue($this->captchaMock));

        $this->captchaMock->expects($this->any())->method('isCaseSensitive')->will($this->returnValue(1));
        $this->captchaMock->expects($this->any())->method('getHeight')->will($this->returnValue('12px'));
        $this->captchaMock->expects($this->any())->method('isRequired')->will($this->returnValue($isRequired));

        $this->captchaMock->expects($this->exactly($captchaGenerations))->method('generate');
        $this->captchaMock->expects($this->exactly($captchaGenerations))->method('getImgSrc')
            ->will($this->returnValue('source'));

        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->storeMock->expects($this->once())->method('isCurrentlySecure')->will($this->returnValue(true));
        $this->storeMock->expects($this->once())->method('getUrl')->with('captcha/refresh', ['_secure' => true])
            ->will($this->returnValue('https://magento.com/captcha'));

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
