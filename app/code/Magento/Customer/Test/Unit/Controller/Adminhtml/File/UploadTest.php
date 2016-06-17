<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Adminhtml\File;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Controller\Adminhtml\File\Upload;
use Magento\Customer\Model\FileProcessor;
use Magento\Framework\Controller\ResultFactory;

class UploadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var FileProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileProcessor;

    /**
     * @var CustomerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerMetadataService;

    /**
     * @var AddressMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressMetadataService;

    /**
     * @var \Magento\Customer\Model\Metadata\ElementFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $elementFactory;

    /**
     * @var Upload
     */
    private $controller;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

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

        $this->fileProcessor = $this->getMockBuilder('Magento\Customer\Model\FileProcessor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerMetadataService = $this->getMockBuilder('Magento\Customer\Api\CustomerMetadataInterface')
            ->getMockForAbstractClass();

        $this->addressMetadataService = $this->getMockBuilder('Magento\Customer\Api\AddressMetadataInterface')
            ->getMockForAbstractClass();

        $this->elementFactory = $this->getMockBuilder('Magento\Customer\Model\Metadata\ElementFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new Upload(
            $this->context,
            $this->fileProcessor,
            $this->customerMetadataService,
            $this->addressMetadataService,
            $this->elementFactory
        );
    }

    public function testExecuteEmptyFiles()
    {
        $resultJson = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->once())
            ->method('setData')
            ->with([
                'error' => '$_FILES array is empty.',
                'errorcode' => 0,
            ])
            ->willReturnSelf();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($resultJson);

        $this->assertInstanceOf('Magento\Framework\Controller\Result\Json', $this->controller->execute());
    }

    public function testExecuteInvalid()
    {
        $attributeCode = 'attribute_code';

        $_FILES = [
            'customer' => [
                'name' => [
                    $attributeCode => 'filename',
                ],
            ],
        ];

        $errors = [
            'error1',
            'error2',
        ];

        $attributeMetadataMock = $this->getMockBuilder('Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->getMockForAbstractClass();

        $this->customerMetadataService->expects($this->once())
            ->method('getAttributeMetadata')
            ->with($attributeCode)
            ->willReturn($attributeMetadataMock);

        $formElement = $this->getMockBuilder('Magento\Customer\Model\Metadata\Form\Image')
            ->disableOriginalConstructor()
            ->getMock();
        $formElement->expects($this->once())
            ->method('validateValue')
            ->with(['name' => 'filename'])
            ->willReturn($errors);

        $this->elementFactory->expects($this->once())
            ->method('create')
            ->with($attributeMetadataMock, null, CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER)
            ->willReturn($formElement);

        $resultJson = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->once())
            ->method('setData')
            ->with([
                'error' => 'error1</br>error2',
                'errorcode' => 0,
            ])
            ->willReturnSelf();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($resultJson);

        $this->assertInstanceOf('Magento\Framework\Controller\Result\Json', $this->controller->execute());
    }

    /**
     * @param array $data
     * @dataProvider dataProviderTestExecute
     */
    public function testExecute(array $data)
    {
        $attributeCode = 'attribute_code';
        $attributeFrontendInput = 'image';

        $_FILES = $data;

        $_FILES = [
            'customer' => [
                'name' => [
                    $attributeCode => 'filename',
                ],
            ],
        ];

        $allowedExtensions = 'ext1,EXT2 , eXt3';    // Added spaces, commas and upper-cases
        $expectedAllowedExtensions = [
            'ext1',
            'ext2',
            'ext3',
        ];

        $resultFileName = '/filename.ext1';
        $resultFilePath = 'filepath';
        $resultFileUrl = 'viewFileUrl';

        $validationRuleMock = $this->getMockBuilder('Magento\Customer\Api\Data\ValidationRuleInterface')
            ->getMockForAbstractClass();
        $validationRuleMock->expects($this->once())
            ->method('getName')
            ->willReturn('file_extensions');
        $validationRuleMock->expects($this->once())
            ->method('getValue')
            ->willReturn($allowedExtensions);

        $this->fileProcessor->expects($this->once())
            ->method('setAllowedExtensions')
            ->with($expectedAllowedExtensions)
            ->willReturnSelf();
        $this->fileProcessor->expects($this->once())
            ->method('saveTemporaryFile')
            ->with('customer[' . $attributeCode . ']')
            ->willReturn([
                'name' => $resultFileName,
                'path' => $resultFilePath,
                'file' => $resultFileName,
            ]);
        $this->fileProcessor->expects($this->once())
            ->method('getViewUrl')
            ->with(FileProcessor::TMP_DIR . '/' . 'filename.ext1', $attributeFrontendInput)
            ->willReturn($resultFileUrl);

        $attributeMetadataMock = $this->getMockBuilder('Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->getMockForAbstractClass();
        $attributeMetadataMock->expects($this->once())
            ->method('getValidationRules')
            ->willReturn([$validationRuleMock]);
        $attributeMetadataMock->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn($attributeFrontendInput);

        $this->customerMetadataService->expects($this->once())
            ->method('getAttributeMetadata')
            ->with($attributeCode)
            ->willReturn($attributeMetadataMock);

        $formElement = $this->getMockBuilder('Magento\Customer\Model\Metadata\Form\Image')
            ->disableOriginalConstructor()
            ->getMock();
        $formElement->expects($this->once())
            ->method('validateValue')
            ->with(['name' => 'filename'])
            ->willReturn(true);

        $this->elementFactory->expects($this->once())
            ->method('create')
            ->with($attributeMetadataMock, null, CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER)
            ->willReturn($formElement);

        $resultJson = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->once())
            ->method('setData')
            ->with([
                'name' => $resultFileName,
                'file' => $resultFileName,
                'path' => $resultFilePath,
                'tmp_name' => $resultFilePath . $resultFileName,
                'url' => $resultFileUrl,
            ])
            ->willReturnSelf();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($resultJson);

        $this->assertInstanceOf('Magento\Framework\Controller\Result\Json', $this->controller->execute());
    }

    /**
     * @return array
     */
    public function dataProviderTestExecute()
    {
        $attributeCode = 'attribute_code';

        return [
            [
                [
                    'customer' => [
                        'name' => [
                            $attributeCode => 'filename',
                        ],
                    ],
                ],
            ],
            [
                [
                    'customer' => [
                        'name' => [
                            1 => [
                                $attributeCode => 'filename',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testExecuteErrorWhileSavingFile()
    {
        $attributeCode = 'attribute_code';

        $_FILES = [
            'customer' => [
                'name' => [
                    $attributeCode => 'filename',
                ],
            ],
        ];

        $allowedExtensions = 'ext1,EXT2 , eXt3';    // Added spaces, commas and upper-cases
        $expectedAllowedExtensions = [
            'ext1',
            'ext2',
            'ext3',
        ];

        $validationRuleMock = $this->getMockBuilder('Magento\Customer\Api\Data\ValidationRuleInterface')
            ->getMockForAbstractClass();
        $validationRuleMock->expects($this->once())
            ->method('getName')
            ->willReturn('file_extensions');
        $validationRuleMock->expects($this->once())
            ->method('getValue')
            ->willReturn($allowedExtensions);

        $this->fileProcessor->expects($this->once())
            ->method('setAllowedExtensions')
            ->with($expectedAllowedExtensions)
            ->willReturnSelf();
        $this->fileProcessor->expects($this->once())
            ->method('saveTemporaryFile')
            ->with('customer[' . $attributeCode . ']')
            ->willReturn(null);

        $attributeMetadataMock = $this->getMockBuilder('Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->getMockForAbstractClass();
        $attributeMetadataMock->expects($this->once())
            ->method('getValidationRules')
            ->willReturn([$validationRuleMock]);

        $this->customerMetadataService->expects($this->once())
            ->method('getAttributeMetadata')
            ->with($attributeCode)
            ->willReturn($attributeMetadataMock);

        $formElement = $this->getMockBuilder('Magento\Customer\Model\Metadata\Form\Image')
            ->disableOriginalConstructor()
            ->getMock();
        $formElement->expects($this->once())
            ->method('validateValue')
            ->with(['name' => 'filename'])
            ->willReturn(true);

        $this->elementFactory->expects($this->once())
            ->method('create')
            ->with($attributeMetadataMock, null, CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER)
            ->willReturn($formElement);

        $resultJson = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->once())
            ->method('setData')
            ->with([
                'error' => __('Something went wrong while saving file.'),
                'errorcode' => null,
            ])
            ->willReturnSelf();

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($resultJson);

        $this->assertInstanceOf('Magento\Framework\Controller\Result\Json', $this->controller->execute());
    }
}
