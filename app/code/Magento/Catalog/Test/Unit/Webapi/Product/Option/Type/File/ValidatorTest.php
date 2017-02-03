<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Webapi\Product\Option\Type\File;

use Magento\Catalog\Model\Webapi\Product\Option\Type\File\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $filesystem;

    /** @var \Magento\Framework\File\Size|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileSize;

    /** @var \Magento\MediaStorage\Helper\File\Storage\Database|\PHPUnit_Framework_MockObject_MockObject */
    protected $coreFileStorageDatabase;

    /** @var \Magento\Catalog\Model\Product\Option\Type\File\ValidateFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $validateFactory;

    /** @var \Magento\Catalog\Model\Product\Option|\PHPUnit_Framework_MockObject_MockObject */
    protected $option;

    /** @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $directoryRead;

    /** @var \Zend_Validate|\PHPUnit_Framework_MockObject_MockObject */
    protected $zendValidator;

    /** @var Validator */
    protected $validator;

    public function setUp()
    {
        $this->scopeConfig = $this->getMockForAbstractClass(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            '',
            false
        );
        $this->filesystem = $this->getMock(
            'Magento\Framework\Filesystem',
            [],
            [],
            '',
            false
        );
        $this->fileSize = $this->getMock(
            'Magento\Framework\File\Size',
            [],
            [],
            '',
            false
        );
        $this->coreFileStorageDatabase = $this->getMock(
            'Magento\MediaStorage\Helper\File\Storage\Database',
            [],
            [],
            '',
            false
        );
        $this->validateFactory = $this->getMock(
            'Magento\Catalog\Model\Product\Option\Type\File\ValidateFactory',
            [],
            [],
            '',
            false
        );
        $this->option = $this->getMock(
            'Magento\Catalog\Model\Product\Option',
            [],
            [],
            '',
            false
        );
        $this->directoryRead = $this->getMockForAbstractClass(
            'Magento\Framework\Filesystem\Directory\ReadInterface',
            [],
            '',
            false
        );
        $this->zendValidator = $this->getMock(
            'Zend_Validate',
            [],
            [],
            '',
            false
        );

        $this->filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with('base')
            ->willReturn($this->directoryRead);

        $this->validator = new Validator(
            $this->scopeConfig,
            $this->filesystem,
            $this->fileSize,
            $this->coreFileStorageDatabase,
            $this->validateFactory
        );
    }

    public function testValidateWithoutOptionValue()
    {
        $this->assertFalse($this->validator->validate('', $this->option));
    }

    public function testValidateWithInvalidOptionValue()
    {
        $this->assertFalse($this->validator->validate([], $this->option));
    }

    protected function prepare()
    {
        $relativePath = '/custom_options/quote/file';
        $absolutePath = '/absolute/path' . $relativePath;

        $this->directoryRead->expects($this->once())
            ->method('isFile')
            ->with('/custom_options/quote/file')
            ->willReturn(true);
        $this->directoryRead->expects($this->once())
            ->method('getAbsolutePath')
            ->with('/custom_options/quote/file')
            ->willReturn($absolutePath);
        $this->validateFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->zendValidator);
        $this->option->expects($this->once())
            ->method('getImageSizeX')
            ->willReturn(0);
        $this->option->expects($this->once())
            ->method('getImageSizeY')
            ->willReturn(0);
        $this->option->expects($this->once())
            ->method('getFileExtension')
            ->willReturn('');
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('catalog/custom_options/forbidden_extensions', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn('');
        $this->fileSize->expects($this->once())
            ->method('getMaxFileSize')
            ->willReturn(9999999);
        $this->zendValidator->expects($this->any())
            ->method('addValidator');
        $this->zendValidator->expects($this->once())
            ->method('isValid')
            ->with($absolutePath)
            ->willReturn(true);
    }

    public function testValidate()
    {
        $relativePath = '/custom_options/quote/file';
        $optionValues = [
            'quote_path' => '/custom_options/quote/file'
        ];
        $this->prepare();

        $this->directoryRead->expects($this->once())
            ->method('isReadable')
            ->with($relativePath)
            ->willReturn(true);
        $this->assertTrue($this->validator->validate($optionValues, $this->option));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The file 'File Title' for 'Option Title' has an invalid extension.
     */
    public function testValidateWithInvalidFile()
    {
        $relativePath = '/custom_options/quote/file';
        $optionValues = [
            'quote_path' => '/custom_options/quote/file',
            'title' => 'File Title'
        ];
        $this->prepare();

        $this->directoryRead->expects($this->once())
            ->method('isReadable')
            ->with($relativePath)
            ->willReturn(false);
        $this->option->expects($this->once())
            ->method('getTitle')
            ->willReturn('Option Title');
        $this->zendValidator->expects($this->at(2))
            ->method('getErrors')
            ->willReturn(true);
        $this->zendValidator->expects($this->at(3))
            ->method('getErrors')
            ->willReturn([\Zend_Validate_File_ExcludeExtension::FALSE_EXTENSION]);
        $this->validator->validate($optionValues, $this->option);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please specify product's required option(s).
     */
    public function testValidateWithUnreadableFile()
    {
        $relativePath = '/custom_options/quote/file';
        $optionValues = [
            'quote_path' => '/custom_options/quote/file',
            'title' => 'File Title'
        ];
        $this->prepare();

        $this->directoryRead->expects($this->once())
            ->method('isReadable')
            ->with($relativePath)
            ->willReturn(false);
        $this->validator->validate($optionValues, $this->option);
    }
}
