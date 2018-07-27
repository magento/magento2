<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Adminhtml\File\Address;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Controller\Adminhtml\File\Address\Upload;
use Magento\Framework\Controller\ResultFactory;

class UploadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Upload
     */
    private $controller;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Customer\Model\FileUploaderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileUploaderFactory;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var AddressMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressMetadataService;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    protected function setUp()
    {
        $this->resultFactory = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);

        $this->fileUploaderFactory = $this->getMockBuilder('Magento\Customer\Model\FileUploaderFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->addressMetadataService = $this->getMockBuilder('Magento\Customer\Api\AddressMetadataInterface')
            ->getMockForAbstractClass();

        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->getMockForAbstractClass();

        $this->controller = new Upload(
            $this->context,
            $this->fileUploaderFactory,
            $this->addressMetadataService,
            $this->logger
        );
    }

    public function testExecuteEmptyFiles()
    {
        $exception = new \Exception('$_FILES array is empty.');
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($exception)
            ->willReturnSelf();

        $resultJson = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
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

        $this->assertInstanceOf('Magento\Framework\Controller\Result\Json', $this->controller->execute());
    }

    public function testExecute()
    {
        $attributeCode = 'attribute_code';

        $_FILES = [
            'address' => [
                'name' => [
                    'new_0' => [
                        $attributeCode => 'filename',
                    ],
                ],
            ],
        ];

        $resultFileName = '/filename.ext1';
        $resultFilePath = 'filepath';
        $resultFileUrl = 'viewFileUrl';

        $result = [
            'name' => $resultFileName,
            'file' => $resultFileName,
            'path' => $resultFilePath,
            'tmp_name' => $resultFilePath . $resultFileName,
            'url' => $resultFileUrl,
        ];

        $attributeMetadataMock = $this->getMockBuilder('Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->getMockForAbstractClass();

        $this->addressMetadataService->expects($this->once())
            ->method('getAttributeMetadata')
            ->with($attributeCode)
            ->willReturn($attributeMetadataMock);

        $fileUploader = $this->getMockBuilder('Magento\Customer\Model\FileUploader')
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

        $resultJson = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
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

        $this->assertInstanceOf('Magento\Framework\Controller\Result\Json', $this->controller->execute());
    }

    public function testExecuteWithErrors()
    {
        $attributeCode = 'attribute_code';

        $_FILES = [
            'address' => [
                'name' => [
                    'new_0' => [
                        $attributeCode => 'filename',
                    ],
                ],
            ],
        ];

        $errors = [
            'error1',
            'error2',
        ];

        $attributeMetadataMock = $this->getMockBuilder('Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->getMockForAbstractClass();

        $this->addressMetadataService->expects($this->once())
            ->method('getAttributeMetadata')
            ->with($attributeCode)
            ->willReturn($attributeMetadataMock);

        $fileUploader = $this->getMockBuilder('Magento\Customer\Model\FileUploader')
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

        $resultJson = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
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

        $this->assertInstanceOf('Magento\Framework\Controller\Result\Json', $this->controller->execute());
    }
}
