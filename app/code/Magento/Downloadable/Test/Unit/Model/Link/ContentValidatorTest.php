<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model\Link;

use Magento\Downloadable\Helper\File;
use Magento\Downloadable\Model\Link\ContentValidator;
use Magento\Downloadable\Model\Url\DomainValidator;

/**
 * Unit tests for Magento\Downloadable\Model\Link\ContentValidator.
 */
class ContentValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContentValidator
     */
    protected $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileValidatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlValidatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkFileMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sampleFileMock;

    /**
     * @var File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileMock;

    /**
     * @var DomainValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $domainValidatorMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->fileValidatorMock = $this->createMock(\Magento\Downloadable\Model\File\ContentValidator::class);
        $this->urlValidatorMock = $this->createMock(\Magento\Framework\Url\Validator::class);
        $this->linkFileMock = $this->createMock(\Magento\Downloadable\Api\Data\File\ContentInterface::class);
        $this->sampleFileMock = $this->createMock(\Magento\Downloadable\Api\Data\File\ContentInterface::class);
        $this->fileMock = $this->createMock(File::class);
        $this->domainValidatorMock = $this->createMock(DomainValidator::class);

        $this->validator = $objectManager->getObject(
            ContentValidator::class,
            [
                'fileContentValidator' => $this->fileValidatorMock,
                'urlValidator' => $this->urlValidatorMock,
                'fileHelper' => $this->fileMock,
                'domainValidator' => $this->domainValidatorMock,
            ]
        );
    }

    public function testIsValid()
    {
        $linkFileContentMock = $this->createMock(\Magento\Downloadable\Api\Data\File\ContentInterface::class);
        $sampleFileContentMock = $this->createMock(\Magento\Downloadable\Api\Data\File\ContentInterface::class);
        $linkData = [
            'title' => 'Title',
            'sort_order' => 1,
            'price' => 10.1,
            'shareable' => true,
            'number_of_downloads' => 100,
            'link_type' => 'file',
            'sample_type' => 'file',
            'link_file_content' => $linkFileContentMock,
            'sample_file_content' => $sampleFileContentMock,
        ];
        $this->fileValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $this->urlValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $this->domainValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $linkMock = $this->getLinkMock($linkData);
        $this->assertTrue($this->validator->isValid($linkMock));
    }

    public function testIsValidSkipLinkContent()
    {
        $sampleFileContentMock = $this->createMock(\Magento\Downloadable\Api\Data\File\ContentInterface::class);
        $linkData = [
            'title' => 'Title',
            'sort_order' => 1,
            'price' => 10.1,
            'shareable' => true,
            'number_of_downloads' => 100,
            'link_type' => 'url',
            'link_url' => 'http://example.com',
            'sample_type' => 'file',
            'sample_file_content' => $sampleFileContentMock,
        ];
        $this->fileValidatorMock->expects($this->once())->method('isValid')->willReturn(true);
        $this->urlValidatorMock->expects($this->never())->method('isValid')->willReturn(true);
        $this->domainValidatorMock->expects($this->never())->method('isValid')->willReturn(true);
        $linkMock = $this->getLinkMock($linkData);
        $this->assertTrue($this->validator->isValid($linkMock, false));
    }

    public function testIsValidSkipSampleContent()
    {
        $sampleFileContentMock = $this->createMock(\Magento\Downloadable\Api\Data\File\ContentInterface::class);
        $linkData = [
            'title' => 'Title',
            'sort_order' => 1,
            'price' => 10.1,
            'shareable' => true,
            'number_of_downloads' => 100,
            'link_type' => 'url',
            'link_url' => 'http://example.com',
            'sample_type' => 'file',
            'sample_file_content' => $sampleFileContentMock,
        ];
        $this->fileValidatorMock->expects($this->never())->method('isValid')->willReturn(true);
        $this->urlValidatorMock->expects($this->once())->method('isValid')->willReturn(true);
        $this->domainValidatorMock->expects($this->once())->method('isValid')->willReturn(true);
        $linkMock = $this->getLinkMock($linkData);
        $this->assertTrue($this->validator->isValid($linkMock, true, false));
    }

    /**
     * @param string|int|float $sortOrder
     * @dataProvider getInvalidSortOrder
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Sort order must be a positive integer.
     */
    public function testIsValidThrowsExceptionIfSortOrderIsInvalid($sortOrder)
    {
        $linkContentData = [
            'title' => 'Title',
            'sort_order' => $sortOrder,
            'price' => 10.1,
            'shareable' => true,
            'number_of_downloads' => 100,
            'link_type' => 'file',
            'sample_type' => 'file',
        ];
        $this->fileValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $this->urlValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $this->domainValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $contentMock = $this->getLinkMock($linkContentData);
        $this->validator->isValid($contentMock);
    }

    /**
     * @return array
     */
    public function getInvalidSortOrder()
    {
        return [
            [-1],
            ['string'],
            [1.1],
        ];
    }

    /**
     * @param string|int|float $price
     * @dataProvider getInvalidPrice
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Link price must have numeric positive value.
     */
    public function testIsValidThrowsExceptionIfPriceIsInvalid($price)
    {
        $linkContentData = [
            'title' => 'Title',
            'sort_order' => 1,
            'price' => $price,
            'shareable' => true,
            'number_of_downloads' => 100,
            'link_type' => 'file',
            'sample_type' => 'file',
        ];
        $this->fileValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $this->urlValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $this->domainValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $contentMock = $this->getLinkMock($linkContentData);
        $this->validator->isValid($contentMock);
    }

    /**
     * @return array
     */
    public function getInvalidPrice()
    {
        return [
            [-1],
            ['string'],
        ];
    }

    /**
     * @param string|int|float $numberOfDownloads
     * @dataProvider getInvalidNumberOfDownloads
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Number of downloads must be a positive integer.
     */
    public function testIsValidThrowsExceptionIfNumberOfDownloadsIsInvalid($numberOfDownloads)
    {
        $linkContentData = [
            'title' => 'Title',
            'sort_order' => 1,
            'price' => 10.5,
            'shareable' => true,
            'number_of_downloads' => $numberOfDownloads,
            'link_type' => 'file',
            'sample_type' => 'file',
        ];
        $this->urlValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $this->fileValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $this->domainValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $contentMock = $this->getLinkMock($linkContentData);
        $this->validator->isValid($contentMock);
    }

    /**
     * @return array
     */
    public function getInvalidNumberOfDownloads()
    {
        return [
            [-1],
            [2.71828],
            ['string'],
        ];
    }

    /**
     * @param array $linkData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLinkMock(array $linkData)
    {
        $linkMock = $this->getMockBuilder(\Magento\Downloadable\Api\Data\LinkInterface::class)
            ->setMethods(
                [
                    'getTitle',
                    'getPrice',
                    'getSortOrder',
                    'isShareable',
                    'getNumberOfDownloads',
                    'getLinkType',
                    'getLinkFile',
                ]
            )
            ->getMockForAbstractClass();
        $linkMock->expects($this->any())->method('getTitle')->willReturn($linkData['title']);
        $linkMock->expects($this->any())->method('getPrice')->willReturn($linkData['price']);
        $linkMock->expects($this->any())->method('getSortOrder')->willReturn($linkData['sort_order']);
        $linkMock->expects($this->any())->method('isShareable')->willReturn($linkData['shareable']);
        $linkMock->expects($this->any())->method('getNumberOfDownloads')->will(
            $this->returnValue($linkData['number_of_downloads'])
        );
        $linkMock->expects($this->any())->method('getLinkType')->willReturn($linkData['link_type']);
        $linkMock->expects($this->any())->method('getLinkFile')->willReturn($this->linkFileMock);
        if (isset($linkData['link_url'])) {
            $linkMock->expects($this->any())->method('getLinkUrl')->willReturn($linkData['link_url']);
        }
        if (isset($linkData['sample_url'])) {
            $linkMock->expects($this->any())->method('getSampleUrl')->willReturn($linkData['sample_url']);
        }
        if (isset($linkData['sample_type'])) {
            $linkMock->expects($this->any())->method('getSampleType')->willReturn($linkData['sample_type']);
        }
        if (isset($linkData['link_file_content'])) {
            $linkMock->expects($this->any())
                ->method('getLinkFileContent')
                ->willReturn($linkData['link_file_content']);
        }
        if (isset($linkData['sample_file_content'])) {
            $linkMock->expects($this->any())
                ->method('getSampleFileContent')
                ->willReturn($linkData['sample_file_content']);
        }
        $linkMock->expects($this->any())->method('getSampleFile')->willReturn($this->sampleFileMock);

        return $linkMock;
    }
}
