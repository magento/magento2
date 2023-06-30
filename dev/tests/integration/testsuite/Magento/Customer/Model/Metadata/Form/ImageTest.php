<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Model\Metadata\Form;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

class ImageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $fileName = 'magento.jpg';

    /**
     * @var string
     */
    private $invalidFileName = '../../invalidFile.xyz';

    /**
     * @var string
     */
    private $imageFixtureDir;

    /**
     * @var string
     */
    private $expectedFileName;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->imageFixtureDir = realpath(__DIR__ . '/../../../_files/image');
        $this->expectedFileName = '/m/a/' . $this->fileName;
    }

    /**
     * Test for processCustomerAddressValue method
     *
     * @magentoAppIsolation enabled
     * @throws FileSystemException
     * @throws \ReflectionException
     */
    public function testProcessCustomerAddressValue()
    {
        $this->mediaDirectory->delete('customer_address');
        $this->mediaDirectory->create($this->mediaDirectory->getRelativePath('customer_address/tmp/'));
        $tmpFilePath = $this->mediaDirectory->getAbsolutePath('customer_address/tmp/' . $this->fileName);
        copy($this->imageFixtureDir . DIRECTORY_SEPARATOR . $this->fileName, $tmpFilePath);

        $imageFile = [
            'name' => $this->fileName,
            'type' => 'image/jpeg',
            'tmp_name' => $this->fileName,
            'file' => $this->fileName,
            'error' => 0,
            'size' => 12500,
            'previewType' => 'image',
        ];

        $params = [
            'entityTypeCode' => 'customer_address',
            'formCode' => 'customer_address_edit',
            'isAjax' => false,
            'value' => $imageFile
        ];

        $expectedPath = $this->mediaDirectory->getAbsolutePath('customer_address' . $this->expectedFileName);

        /** @var Image $image */
        $image = $this->objectManager->create(\Magento\Customer\Model\Metadata\Form\Image::class, $params);
        $processCustomerAddressValueMethod = new \ReflectionMethod(
            \Magento\Customer\Model\Metadata\Form\Image::class,
            'processCustomerAddressValue'
        );
        $processCustomerAddressValueMethod->setAccessible(true);
        $actual = $processCustomerAddressValueMethod->invoke($image, $imageFile);
        $this->assertEquals($this->expectedFileName, $actual);
        $this->assertFileExists($expectedPath);
        $this->assertFileDoesNotExist($tmpFilePath);
    }

    /**
     * Test for processCustomerValue method
     *
     * @magentoAppIsolation enabled
     * @throws FileSystemException
     * @throws \ReflectionException
     */
    public function testProcessCustomerValue()
    {
        $this->mediaDirectory->delete('customer');
        $this->mediaDirectory->create($this->mediaDirectory->getRelativePath('customer/tmp/'));
        $tmpFilePath = $this->mediaDirectory->getAbsolutePath('customer/tmp/' . $this->fileName);
        copy($this->imageFixtureDir . DIRECTORY_SEPARATOR . $this->fileName, $tmpFilePath);

        $imageFile = [
            'name' => $this->fileName,
            'type' => 'image/jpeg',
            'tmp_name' => $this->fileName,
            'file' => $this->fileName,
            'error' => 0,
            'size' => 12500,
            'previewType' => 'image',
        ];

        $params = [
            'entityTypeCode' => 'customer',
            'formCode' => 'customer_edit',
            'isAjax' => false,
            'value' => $imageFile
        ];

        /** @var Image $image */
        $image = $this->objectManager->create(\Magento\Customer\Model\Metadata\Form\Image::class, $params);
        $processCustomerAddressValueMethod = new \ReflectionMethod(
            \Magento\Customer\Model\Metadata\Form\Image::class,
            'processCustomerValue'
        );
        $processCustomerAddressValueMethod->setAccessible(true);
        $result = $processCustomerAddressValueMethod->invoke($image, $imageFile);
        $this->assertInstanceOf('Magento\Framework\Api\ImageContent', $result);
        $this->assertFileDoesNotExist($tmpFilePath);
    }

    /**
     * Test for processCustomerValue method with invalid value
     *
     * @magentoAppIsolation enabled
     *
     * @throws FileSystemException
     * @throws \ReflectionException
     */
    public function testProcessCustomerInvalidValue()
    {
        $this->expectException(
            \Magento\Framework\Exception\ValidatorException::class
        );

        $this->mediaDirectory->delete('customer');
        $this->mediaDirectory->create($this->mediaDirectory->getRelativePath('customer/tmp/'));
        $tmpFilePath = $this->mediaDirectory->getAbsolutePath('customer/tmp/' . $this->fileName);
        copy($this->imageFixtureDir . DIRECTORY_SEPARATOR . $this->fileName, $tmpFilePath);

        $imageFile = [
            'name' => $this->fileName,
            'type' => 'image/jpeg',
            'tmp_name' => $this->fileName,
            'file' => $this->invalidFileName,
            'error' => 0,
            'size' => 12500,
            'previewType' => 'image',
        ];

        $params = [
            'entityTypeCode' => 'customer',
            'formCode' => 'customer_edit',
            'isAjax' => false,
            'value' => $imageFile
        ];

        /** @var Image $image */
        $image = $this->objectManager->create(\Magento\Customer\Model\Metadata\Form\Image::class, $params);
        $processCustomerAddressValueMethod = new \ReflectionMethod(
            \Magento\Customer\Model\Metadata\Form\Image::class,
            'processCustomerValue'
        );
        $processCustomerAddressValueMethod->setAccessible(true);
        $processCustomerAddressValueMethod->invoke($image, $imageFile);
    }

    /**
     * @inheritdoc
     * @throws FileSystemException
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            Filesystem::class
        );
        /** @var WriteInterface $mediaDirectory */
        $mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->delete('customer');
        $mediaDirectory->delete('customer_address');
    }
}
