<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\FileProcessor;
use Magento\Framework\App\Filesystem\DirectoryList;

class FileProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Url\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlEncoder;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mediaDirectory;

    protected function setUp()
    {
        $this->mediaDirectory = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\WriteInterface')
            ->getMockForAbstractClass();

        $this->filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectory);

        $this->urlBuilder = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->getMockForAbstractClass();

        $this->urlEncoder = $this->getMockBuilder('Magento\Framework\Url\EncoderInterface')
            ->getMockForAbstractClass();
    }

    /**
     * @param string $entityTypeCode
     * @return FileProcessor
     */
    private function getModel($entityTypeCode)
    {
        $model = new FileProcessor(
            $this->filesystem,
            $this->urlBuilder,
            $this->urlEncoder,
            $entityTypeCode
        );

        return $model;
    }

    public function testIsExist()
    {
        $fileName = '/filename.ext1';

        $this->mediaDirectory->expects($this->once())
            ->method('isExist')
            ->with(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . $fileName)
            ->willReturn(true);

        $this->model = new FileProcessor(
            $this->filesystem,
            $this->urlBuilder,
            $this->urlEncoder,
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER
        );

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $this->assertTrue($model->isExist($fileName));
    }

    public function testGetViewUrlCustomer()
    {
        $filePath = 'filename.ext1';
        $encodedFilePath = 'encodedfilenameext1';

        $fileUrl = 'fileUrl';

        $this->urlEncoder->expects($this->once())
            ->method('encode')
            ->with($filePath)
            ->willReturn($encodedFilePath);

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('customer/index/viewfile', ['image' => $encodedFilePath])
            ->willReturn($fileUrl);

        $model = $this->getModel(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $this->assertEquals($fileUrl, $model->getViewUrl($filePath, 'image'));
    }

    public function testGetViewUrlCustomerAddress()
    {
        $filePath = 'filename.ext1';

        $baseUrl = 'baseUrl';
        $relativeUrl = 'relativeUrl';

        $this->urlBuilder->expects($this->once())
            ->method('getBaseUrl')
            ->with(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA])
            ->willReturn($baseUrl);

        $this->mediaDirectory->expects($this->once())
            ->method('getRelativePath')
            ->with(AddressMetadataInterface::ENTITY_TYPE_ADDRESS . '/' . $filePath)
            ->willReturn($relativeUrl);

        $model = $this->getModel(AddressMetadataInterface::ENTITY_TYPE_ADDRESS);

        $this->assertEquals($baseUrl . $relativeUrl, $model->getViewUrl($filePath, 'image'));
    }
}
