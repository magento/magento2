<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\FileProcessor;
use Magento\Customer\Model\FileUploader;

class FileUploaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerMetadataService;

    /**
     * @var \Magento\Customer\Api\AddressMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressMetadataService;

    /**
     * @var \Magento\Customer\Model\Metadata\ElementFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $elementFactory;

    /**
     * @var \Magento\Customer\Model\FileProcessorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileProcessorFactory;

    /**
     * @var \Magento\Customer\Api\Data\AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMetadata;

    protected function setUp()
    {
        $this->customerMetadataService = $this->getMockBuilder('Magento\Customer\Api\CustomerMetadataInterface')
            ->getMockForAbstractClass();

        $this->addressMetadataService = $this->getMockBuilder('Magento\Customer\Api\AddressMetadataInterface')
            ->getMockForAbstractClass();

        $this->elementFactory = $this->getMockBuilder('Magento\Customer\Model\Metadata\ElementFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileProcessorFactory = $this->getMockBuilder('Magento\Customer\Model\FileProcessorFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->attributeMetadata = $this->getMockBuilder('Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->getMockForAbstractClass();
    }

    /**
     * @param string $entityTypeCode
     * @param string $scope
     * @return FileUploader
     */
    private function getModel($entityTypeCode, $scope)
    {
        $model = new FileUploader(
            $this->customerMetadataService,
            $this->addressMetadataService,
            $this->elementFactory,
            $this->fileProcessorFactory,
            $this->attributeMetadata,
            $entityTypeCode,
            $scope
        );
        return $model;
    }

    public function testValidate()
    {
        $attributeCode = 'attribute_code';

        $filename = 'filename.ext1';

        $_FILES = [
            'customer' => [
                'name' => [
                    $attributeCode => $filename,
                ],
            ],
        ];

        $formElement = $this->getMockBuilder('Magento\Customer\Model\Metadata\Form\Image')
            ->disableOriginalConstructor()
            ->getMock();
        $formElement->expects($this->once())
            ->method('validateValue')
            ->with(['name' => $filename])
            ->willReturn(true);

        $this->elementFactory->expects($this->once())
            ->method('create')
            ->with($this->attributeMetadata, null, CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER)
            ->willReturn($formElement);

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, 'customer');
        $this->assertTrue($model->validate());
    }

    public function testUpload()
    {
        $attributeCode = 'attribute_code';
        $attributeFrontendInput = 'image';

        $resultFileName = '/filename.ext1';
        $resultFilePath = 'filepath';
        $resultFileUrl = 'viewFileUrl';

        $allowedExtensions = 'ext1,EXT2 , eXt3';    // Added spaces, commas and upper-cases
        $expectedAllowedExtensions = [
            'ext1',
            'ext2',
            'ext3',
        ];

        $_FILES = [
            'customer' => [
                'name' => [
                    $attributeCode => 'filename',
                ],
            ],
        ];

        $expectedResult = [
            'name' => $resultFileName,
            'file' => $resultFileName,
            'path' => $resultFilePath,
            'tmp_name' => $resultFilePath . $resultFileName,
            'url' => $resultFileUrl,
        ];

        $fileProcessor = $this->getMockBuilder('Magento\Customer\Model\FileProcessor')
            ->disableOriginalConstructor()
            ->getMock();
        $fileProcessor->expects($this->once())
            ->method('saveTemporaryFile')
            ->with('customer[' . $attributeCode . ']')
            ->willReturn([
                'name' => $resultFileName,
                'path' => $resultFilePath,
                'file' => $resultFileName,
            ]);
        $fileProcessor->expects($this->once())
            ->method('getViewUrl')
            ->with(FileProcessor::TMP_DIR . '/filename.ext1', $attributeFrontendInput)
            ->willReturn($resultFileUrl);

        $this->fileProcessorFactory->expects($this->once())
            ->method('create')
            ->with([
                'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'allowedExtensions' => $expectedAllowedExtensions,
            ])
            ->willReturn($fileProcessor);

        $validationRuleMock = $this->getMockBuilder('Magento\Customer\Api\Data\ValidationRuleInterface')
            ->getMockForAbstractClass();
        $validationRuleMock->expects($this->once())
            ->method('getName')
            ->willReturn('file_extensions');
        $validationRuleMock->expects($this->once())
            ->method('getValue')
            ->willReturn($allowedExtensions);

        $this->attributeMetadata->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn($attributeFrontendInput);
        $this->attributeMetadata->expects($this->once())
            ->method('getValidationRules')
            ->willReturn([$validationRuleMock]);


        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, 'customer');
        $this->assertEquals($expectedResult, $model->upload());
    }
}
