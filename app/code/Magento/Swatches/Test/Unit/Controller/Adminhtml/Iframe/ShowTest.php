<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Controller\Adminhtml\Iframe;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class to show swatch image and save it on disk
 */
class ShowTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\App\Action\Context */
    protected $contextMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Response */
    protected $responseMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Swatches\Helper\Media */
    protected $swatchHelperMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Image\AdapterFactory */
    protected $adapterFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Image\Adapter */
    protected $adapterMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Media\Config */
    protected $configMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem */
    protected $filesystemMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\Read */
    protected $mediaDirectoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\MediaStorage\Model\File\UploaderFactory */
    protected $uploaderFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\MediaStorage\Model\File\Uploader */
    protected $uploaderMock;

    /** @var ObjectManager|\Magento\Swatches\Controller\Adminhtml\Iframe\Show */
    protected $controller;

    protected function setUp()
    {
        $this->contextMock = $this->getMock('\Magento\Backend\App\Action\Context', [], [], '', false);
        $observerMock = $this->getMock('\Magento\Framework\Event\Manager', [], [], '', false);
        $this->responseMock = $this->getMock('\Magento\Framework\App\Response', ['setBody'], [], '', false);
        $this->contextMock->expects($this->once())->method('getEventManager')->willReturn($observerMock);
        $this->contextMock->expects($this->once())->method('getResponse')->willReturn($this->responseMock);
        $this->swatchHelperMock = $this->getMock('\Magento\Swatches\Helper\Media', [], [], '', false);
        $this->adapterFactoryMock = $this->getMock(
            '\Magento\Framework\Image\AdapterFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->configMock = $this->getMock('\Magento\Catalog\Model\Product\Media\Config', [], [], '', false);
        $this->filesystemMock = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $this->uploaderFactoryMock = $this->getMock(
            '\Magento\MediaStorage\Model\File\UploaderFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->uploaderMock = $this->getMock('\Magento\MediaStorage\Model\File\Uploader', [], [], '', false);
        $this->adapterMock = $this->getMock('\Magento\Framework\Image\Adapter', [], [], '', false);
        $this->mediaDirectoryMock = $this->getMock('\Magento\Framework\Filesystem\Directory\Read', [], [], '', false);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->controller = $objectManager->getObject(
            '\Magento\Swatches\Controller\Adminhtml\Iframe\Show',
            [
                'context' => $this->contextMock,
                'swatchHelper' => $this->swatchHelperMock,
                'adapterFactory' => $this->adapterFactoryMock,
                'config' => $this->configMock,
                'filesystem' => $this->filesystemMock,
                'uploaderFactory' => $this->uploaderFactoryMock,
            ]
        );

    }

    public function testExecuteException()
    {
        $this->uploaderFactoryMock
            ->expects($this->once())
            ->method('create')
            ->will($this->throwException(new \Exception));
        $this->controller->execute();
    }

    /**
     * @dataProvider dataForExecute
     */
    public function testExecute($fileResult, $expectedResult)
    {
        $this->uploaderFactoryMock->expects($this->once())->method('create')->willReturn($this->uploaderMock);
        $this->adapterFactoryMock->expects($this->once())->method('create')->willReturn($this->adapterMock);
        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryRead')
            ->with('media')
            ->willReturn($this->mediaDirectoryMock);

        $this->uploaderMock->expects($this->once())->method('save')->willReturn($fileResult);

        $this->configMock
            ->expects($this->once())
            ->method('getTmpMediaUrl')
            ->with($fileResult['file'])
            ->willReturn('http://domain.com/tpm_dir/m/a/magento.png');
        $this->swatchHelperMock
            ->expects($this->once())
            ->method('moveImageFromTmp')
            ->with('/m/a/magento.png.tmp')
            ->willReturn('/m/a/magento.png');
        $this->swatchHelperMock->expects($this->once())->method('generateSwatchVariations');
        $this->swatchHelperMock
            ->expects($this->once())
            ->method('getSwatchMediaUrl')
            ->willReturn('http://domain.com/media/path/');

        $this->responseMock->expects($this->once())->method('setBody')->willReturn(json_encode($expectedResult));

        $this->controller->execute();
    }

    public function dataForExecute()
    {
        return [
            [
                [
                    'name' => 'magento.png',
                    'type' => 'image/png',
                    'tmp_name' => '/tmp/sdgsergdf',
                    'error' => 0,
                    'size' => 43233,
                    'path' => '/full/path/to/dir',
                    'file' => '/m/a/magento.png',
                ],
                [
                    'name' => 'magento.png',
                    'type' => 'image/png',
                    'tmp_name' => '/tmp/sdgsergdf',
                    'error' => 0,
                    'size' => 43233,
                    'file' => '/m/a/magento.png.tmp',
                    'url' => 'http://domain.com/tpm_dir/m/a/magento.png',
                ]
            ],
        ];
    }
}
