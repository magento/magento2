<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model;

use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Source;
use Magento\Payment\Model\CcConfig;
use Magento\Payment\Model\CcConfigProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CcConfigProviderTest extends TestCase
{
    /**
     * @var CcConfigProvider
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $ccConfigMock;

    /**
     * @var MockObject
     */
    protected $assetSourceMock;

    protected function setUp(): void
    {
        $this->ccConfigMock = $this->createMock(CcConfig::class);
        $this->assetSourceMock = $this->createMock(Source::class);
        $this->model = new CcConfigProvider(
            $this->ccConfigMock,
            $this->assetSourceMock
        );
    }

    public function testGetConfig()
    {
        $imagesDirectoryPath = __DIR__ . '/../../../view/base/web/images/cc/';
        $expectedResult = [
            'payment' => [
                'ccform' => [
                    'icons' => [
                        'vi' => [
                            'url' => 'http://cc.card/vi.png',
                            'width' => getimagesize($imagesDirectoryPath . 'vi.png')[0],
                            'height' => getimagesize($imagesDirectoryPath . 'vi.png')[1],
                            'title' => __('Visa'),
                        ],
                        'ae' => [
                            'url' => 'http://cc.card/ae.png',
                            'width' => getimagesize($imagesDirectoryPath . 'ae.png')[0],
                            'height' => getimagesize($imagesDirectoryPath . 'ae.png')[1],
                            'title' => __('American Express'),
                        ]
                    ]
                ]
            ]
        ];

        $ccAvailableTypesMock = [
            'vi' => [
                'title' => 'Visa',
                'fileId' => 'Magento_Payment::images/cc/vi.png',
                'path' => $imagesDirectoryPath . 'vi.png',
                'url' => 'http://cc.card/vi.png'
            ],
            'ae' => [
                'title' => 'American Express',
                'fileId' => 'Magento_Payment::images/cc/ae.png',
                'path' => $imagesDirectoryPath . 'ae.png',
                'url' => 'http://cc.card/ae.png'
            ]
        ];
        $assetMock = $this->createMock(File::class);

        $this->ccConfigMock->expects($this->once())->method('getCcAvailableTypes')
            ->willReturn(array_combine(
                array_keys($ccAvailableTypesMock),
                array_column($ccAvailableTypesMock, 'title')
            ));

        $this->ccConfigMock->expects($this->atLeastOnce())
            ->method('createAsset')
            ->willReturnCallback(function ($arg1) use ($ccAvailableTypesMock, $assetMock) {
                if ($arg1 == $ccAvailableTypesMock['vi']['fileId'] || $arg1 == $ccAvailableTypesMock['ae']['fileId']) {
                     return $assetMock;
                }
            });
        $this->assetSourceMock->expects($this->atLeastOnce())
            ->method('findSource')
            ->with($assetMock)
            ->willReturnOnConsecutiveCalls(
                $ccAvailableTypesMock['vi']['path'],
                $ccAvailableTypesMock['ae']['path']
            );
        $assetMock->expects($this->atLeastOnce())
            ->method('getSourceFile')
            ->willReturnOnConsecutiveCalls(
                $ccAvailableTypesMock['vi']['path'],
                $ccAvailableTypesMock['ae']['path']
            );
        $assetMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->willReturnOnConsecutiveCalls(
                $ccAvailableTypesMock['vi']['url'],
                $ccAvailableTypesMock['ae']['url']
            );

        $this->assertEquals($expectedResult, $this->model->getConfig());
    }
}
