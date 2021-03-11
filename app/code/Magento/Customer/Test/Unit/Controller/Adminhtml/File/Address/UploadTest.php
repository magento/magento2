<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Adminhtml\File\Address;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Controller\Adminhtml\File\Address\Upload;
use Magento\Framework\Controller\ResultFactory;

class UploadTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Upload
     */
    private $controller;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var \Magento\Customer\Model\FileUploaderFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fileUploaderFactory;

    /**
     * @var ResultFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultFactory;

    /**
     * @var AddressMetadataInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $addressMetadataService;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    protected function setUp(): void
    {
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);

        $this->fileUploaderFactory = $this->getMockBuilder(\Magento\Customer\Model\FileUploaderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->addressMetadataService = $this->getMockBuilder(\Magento\Customer\Api\AddressMetadataInterface::class)
            ->getMockForAbstractClass();

        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();

        $this->controller = new Upload(
            $this->context,
            $this->fileUploaderFactory,
            $this->addressMetadataService,
            $this->logger,
            'address'
        );
    }

    public function testExecuteEmptyFiles()
    {
        $this->markTestSkipped();
        $exception = new \Exception('$_FILES array is empty.');
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($exception)
            ->willReturnSelf();

        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->once())
            ->method('setData')
            ->with([
                'error' => __('Something went wrong while saving file.'),
                'errorcode' => 0,
            ])
            ->willReturnSelf();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($resultJson);

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->controller->execute());
    }

    public function testExecute()
    {
        $attributeCode = 'file_address_attribute';
        $resultFileSize = 20000;
        $resultFileName = 'text.txt';
        $resultType = 'text/plain';

        $_FILES = [
            $attributeCode => [
                'name' => $resultFileName,
                'type' => $resultType,
                'size' => $resultFileSize
            ],
        ];

        $resultFilePath = 'filepath';
        $resultFileUrl = 'viewFileUrl';

        $result = [
            'name' => $resultFileName,
            'type' => $resultType,
            'size' => $resultFileSize,
            'tmp_name' => $resultFilePath . '/' . $resultFileName,
            'url' => $resultFileUrl,
        ];

        $attributeMetadataMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AttributeMetadataInterface::class)
            ->getMockForAbstractClass();

        $this->addressMetadataService->expects($this->once())
            ->method('getAttributeMetadata')
            ->with($attributeCode)
            ->willReturn($attributeMetadataMock);

        $fileUploader = $this->getMockBuilder(\Magento\Customer\Model\FileUploader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileUploader->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $fileUploader->expects($this->once())
            ->method('upload')
            ->willReturn($result);

        $this->fileUploaderFactory->expects($this->once())
            ->method('create')
            ->with([
                'attributeMetadata' => $attributeMetadataMock,
                'entityTypeCode' => AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                'scope' => 'address',
            ])
            ->willReturn($fileUploader);

        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->once())
            ->method('setData')
            ->with($result)
            ->willReturnSelf();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($resultJson);

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->controller->execute());
    }

    public function testExecuteWithErrors()
    {
        $attributeCode = 'file_address_attribute';
        $resultFileSize = 20000;
        $resultFileName = 'text.txt';
        $resultType = 'text/plain';

        $_FILES = [
            $attributeCode => [
                'name' => $resultFileName,
                'type' => $resultType,
                'size' => $resultFileSize
            ],
        ];

        $errors = [
            'error1',
            'error2',
        ];

        $attributeMetadataMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AttributeMetadataInterface::class)
            ->getMockForAbstractClass();

        $this->addressMetadataService->expects($this->once())
            ->method('getAttributeMetadata')
            ->with($attributeCode)
            ->willReturn($attributeMetadataMock);

        $fileUploader = $this->getMockBuilder(\Magento\Customer\Model\FileUploader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileUploader->expects($this->once())
            ->method('validate')
            ->willReturn($errors);

        $this->fileUploaderFactory->expects($this->once())
            ->method('create')
            ->with([
                'attributeMetadata' => $attributeMetadataMock,
                'entityTypeCode' => AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                'scope' => 'address',
            ])
            ->willReturn($fileUploader);

        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->once())
            ->method('setData')
            ->with([
                'error' => implode('</br>', $errors),
                'errorcode' => 0,
            ])
            ->willReturnSelf();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($resultJson);

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->controller->execute());
    }
}
