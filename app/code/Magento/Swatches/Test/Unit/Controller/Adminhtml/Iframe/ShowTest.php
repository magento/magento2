<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Controller\Adminhtml\Iframe;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Event\Manager;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Image\Adapter\AdapterInterface;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Swatches\Controller\Adminhtml\Iframe\Show;
use Magento\Swatches\Helper\Media;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class to show swatch image and save it on disk
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShowTest extends TestCase
{
    /** @var MockObject|Context */
    protected $contextMock;

    /** @var MockObject|\Magento\Framework\App\Response */
    protected $responseMock;

    /** @var MockObject|Media */
    protected $swatchHelperMock;

    /** @var MockObject|AdapterFactory */
    protected $adapterFactoryMock;

    /** @var MockObject|\Magento\Framework\Image\Adapter */
    protected $adapterMock;

    /** @var MockObject|Config */
    protected $configMock;

    /** @var MockObject|Filesystem */
    protected $filesystemMock;

    /** @var MockObject|Read */
    protected $mediaDirectoryMock;

    /** @var MockObject|UploaderFactory */
    protected $uploaderFactoryMock;

    /** @var MockObject|Uploader */
    protected $uploaderMock;

    /** @var ObjectManager|Show */
    protected $controller;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $observerMock = $this->createMock(Manager::class);
        $this->responseMock = $this->createPartialMock(Http::class, ['setBody']);
        $this->contextMock->expects($this->once())->method('getEventManager')->willReturn($observerMock);
        $this->contextMock->expects($this->once())->method('getResponse')->willReturn($this->responseMock);
        $this->swatchHelperMock = $this->createMock(Media::class);
        $this->adapterFactoryMock = $this->createPartialMock(
            AdapterFactory::class,
            ['create']
        );
        $this->configMock = $this->createMock(Config::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->uploaderFactoryMock = $this->createPartialMock(
            UploaderFactory::class,
            ['create']
        );

        $this->uploaderMock = $this->createMock(Uploader::class);
        $this->adapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->mediaDirectoryMock = $this->createMock(Read::class);

        $objectManager = new ObjectManager($this);

        $this->controller = $objectManager->getObject(
            Show::class,
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
            ->willThrowException(new \Exception());
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

    /**
     * @return array
     */
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
