<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model;

class CcConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Payment\Model\CcConfigProvider
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ccConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetSourceMock;

    protected function setUp()
    {
        $this->ccConfigMock = $this->createMock(\Magento\Payment\Model\CcConfig::class);
        $this->assetSourceMock = $this->createMock(\Magento\Framework\View\Asset\Source::class);
        $this->model = new \Magento\Payment\Model\CcConfigProvider(
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
        $assetMock = $this->createMock(\Magento\Framework\View\Asset\File::class);

        $this->ccConfigMock->expects($this->once())->method('getCcAvailableTypes')
            ->willReturn(array_combine(
                array_keys($ccAvailableTypesMock),
                array_column($ccAvailableTypesMock, 'title')
            ));

        $this->ccConfigMock->expects($this->atLeastOnce())
            ->method('createAsset')
            ->withConsecutive(
                [$ccAvailableTypesMock['vi']['fileId']],
                [$ccAvailableTypesMock['ae']['fileId']]
            )->willReturn($assetMock);
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
