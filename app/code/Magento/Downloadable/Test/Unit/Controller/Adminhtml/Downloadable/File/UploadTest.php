<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Controller\Adminhtml\Downloadable\File;

use Magento\Backend\App\Action\Context;
use Magento\Downloadable\Controller\Adminhtml\Downloadable\File\Upload;
use Magento\Downloadable\Helper\File;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Sample;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UploadTest extends TestCase
{
    /** @var Upload */
    protected $upload;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var MockObject|RequestInterface
     */
    protected $request;

    /**
     * @var MockObject|ResponseInterface
     */
    protected $response;

    /**
     * @var MockObject|Link
     */
    protected $link;

    /**
     * @var MockObject|Sample
     */
    protected $sample;

    /**
     * @var MockObject|Context
     */
    protected $context;

    /**
     * @var MockObject|UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var MockObject|Database
     */
    private $storageDatabase;

    /**
     * @var MockObject|File
     */
    protected $fileHelper;

    /**
     * @var MockObject|ResultFactory
     */
    protected $resultFactory;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->storageDatabase = $this->createPartialMock(Database::class, ['saveFile']);
        $this->uploaderFactory = $this->createPartialMock(UploaderFactory::class, ['create']);
        $this->resultFactory = $this->createPartialMock(ResultFactory::class, ['create']);
        $this->context = $this->createMock(Context::class);
        $this->request = $this->createMock(RequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->fileHelper = $this->createPartialMock(File::class, [
            'uploadFromTmp'
        ]);
        $this->context->method('getRequest')->willReturn($this->request);
        $this->context->method('getResultFactory')->willReturn($this->resultFactory);

        $this->link = $this->createMock(Link::class);
        $this->sample = $this->createMock(Sample::class);

        $this->upload = $this->objectManagerHelper->getObject(
            Upload::class,
            [
                'context' => $this->context,
                'link' => $this->link,
                'sample' => $this->sample,
                'fileHelper' => $this->fileHelper,
                'uploaderFactory' => $this->uploaderFactory,
                'storageDatabase' => $this->storageDatabase
            ]
        );
    }

    public function testExecute()
    {
        $data = [
            'tmp_name' => 'tmp_name',
            'path' => 'path',
            'file' => 'file'
        ];
        $uploader = $this->createMock(Uploader::class);
        $resultJson = $this->createPartialMock(Json::class, ['setData']);
        $this->request->expects($this->once())->method('getParam')->with('type')->willReturn('samples');
        $this->sample->expects($this->once())->method('getBaseTmpPath')->willReturn('base_tmp_path');
        $this->uploaderFactory->expects($this->once())->method('create')->willReturn($uploader);
        $this->fileHelper->expects($this->once())->method('uploadFromTmp')->willReturn($data);
        $this->storageDatabase->expects($this->once())->method('saveFile');
        $this->resultFactory->expects($this->once())->method('create')->willReturn($resultJson);
        $resultJson->expects($this->once())->method('setData')->willReturnSelf();

        $this->assertEquals($resultJson, $this->upload->execute());
    }
}
