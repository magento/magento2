<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Option\Type\File;

/**
 * @magentoDataFixture Magento/Catalog/_files/validate_image_info.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidatorInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ValidatorInfo
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /** @var int */
    protected $maxFileSizeInMb;

    /**
     * @var \Magento\Catalog\Model\Product\Option\Type\File\ValidateFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validateFactoryMock;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\File\Size $fileSize */
        $fileSize = $this->objectManager->create(\Magento\Framework\File\Size::class);
        $this->maxFileSizeInMb = $fileSize->getMaxFileSizeInMb();

        $this->validateFactoryMock = $this->getMock(
            \Magento\Catalog\Model\Product\Option\Type\File\ValidateFactory::class,
            ['create']
        );
        $this->model = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Option\Type\File\ValidatorInfo::class,
            [
                'validateFactory' => $this->validateFactoryMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExceptionWithErrors()
    {
        $this->setExpectedException(
            \Magento\Framework\Exception\LocalizedException::class,
            "The file 'test.jpg' for 'MediaOption' has an invalid extension.\n"
            . "The file 'test.jpg' for 'MediaOption' has an invalid extension.\n"
            . "The maximum allowed image size for 'MediaOption' is 2000x2000 px.\n"
            . sprintf(
                "The file 'test.jpg' you uploaded is larger than the %s megabytes allowed by our server.",
                $this->maxFileSizeInMb
            )
        );

        $validateMock = $this->getMock(\Zend_Validate::class, ['isValid', 'getErrors']);
        $validateMock->expects($this->once())->method('isValid')->will($this->returnValue(false));
        $validateMock->expects($this->exactly(2))->method('getErrors')->will($this->returnValue([
            \Zend_Validate_File_ExcludeExtension::FALSE_EXTENSION,
            \Zend_Validate_File_Extension::FALSE_EXTENSION,
            \Zend_Validate_File_ImageSize::WIDTH_TOO_BIG,
            \Zend_Validate_File_FilesSize::TOO_BIG,
        ]));
        $this->validateFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($validateMock));

        $this->model->validate(
            $this->getOptionValue(),
            $this->getProductOption()
        );
    }

    /**
     * @return void
     */
    public function testExceptionWithoutErrors()
    {
        $this->setExpectedException(
            \Magento\Framework\Exception\LocalizedException::class,
            "Please specify product's required option(s)."
        );

        $validateMock = $this->getMock(\Zend_Validate::class, ['isValid', 'getErrors']);
        $validateMock->expects($this->once())->method('isValid')->will($this->returnValue(false));
        $validateMock->expects($this->exactly(1))->method('getErrors')->will($this->returnValue(false));
        $this->validateFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($validateMock));

        $this->model->validate(
            $this->getOptionValue(),
            $this->getProductOption()
        );
    }

    /**
     * @return void
     */
    public function testValidate()
    {
        $validateMock = $this->getMock(\Zend_Validate::class, ['isValid']);
        $validateMock->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $this->validateFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($validateMock));
        $this->assertTrue(
            $this->model->validate(
                $this->getOptionValue(),
                $this->getProductOption()
            )
        );
    }

    /**
     * @param array $options
     * @return \Magento\Catalog\Model\Product\Option
     */
    protected function getProductOption(array $options = [])
    {
        $data = [
            'option_id' => '1',
            'product_id' => '4',
            'type' => 'file',
            'is_require' => '1',
            'sku' => null,
            'max_characters' => null,
            'file_extension' => null,
            'image_size_x' => '2000',
            'image_size_y' => '2000',
            'sort_order' => '0',
            'default_title' => 'MediaOption',
            'store_title' => null,
            'title' => 'MediaOption',
            'default_price' => '5.0000',
            'default_price_type' => 'fixed',
            'store_price' => null,
            'store_price_type' => null,
            'price' => '5.0000',
            'price_type' => 'fixed',
        ];
        $option = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Option::class,
            [
                'data' => array_merge($data, $options)
            ]
        );

        return $option;
    }

    /**
     * @return array
     */
    protected function getOptionValue()
    {
        /** @var \Magento\Catalog\Model\Product\Media\Config $config */
        $config = $this->objectManager->get(\Magento\Catalog\Model\Product\Media\Config::class);
        $file = $config->getBaseTmpMediaPath() . '/magento_small_image.jpg';

        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $tmpDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $filePath = $tmpDirectory->getAbsolutePath($file);

        return [
            'title'      => 'test.jpg',
            'quote_path' => $file,
            'order_path' => $file,
            'secret_key' => substr(md5(file_get_contents($filePath)), 0, 20),
        ];
    }
}
