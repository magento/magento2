<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model\Link;

use Magento\Downloadable\Helper\File;
use Magento\Downloadable\Model\Link\ContentValidator;

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
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileValidatorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlValidatorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $domainValidatorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $linkFileMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $sampleFileMock;

    /**
     * @var File|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fileMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->fileValidatorMock = $this->createMock(\Magento\Downloadable\Model\File\ContentValidator::class);
        $this->urlValidatorMock = $this->createMock(\Magento\Framework\Url\Validator::class);
        $this->domainValidatorMock = $this->createMock(\Magento\Downloadable\Model\Url\DomainValidator::class);
        $this->linkFileMock = $this->createMock(\Magento\Downloadable\Api\Data\File\ContentInterface::class);
        $this->sampleFileMock = $this->createMock(\Magento\Downloadable\Api\Data\File\ContentInterface::class);
        $this->fileMock = $this->createMock(File::class);

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
     */
    public function testIsValidThrowsExceptionIfSortOrderIsInvalid($sortOrder)
    {
        $this->expectException(\Magento\Framework\Exception\InputException::class);
        $this->expectExceptionMessage('Sort order must be a positive integer.');

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
     */
    public function testIsValidThrowsExceptionIfPriceIsInvalid($price)
    {
        $this->expectException(\Magento\Framework\Exception\InputException::class);
        $this->expectExceptionMessage('Link price must have numeric positive value.');

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
     */
    public function testIsValidThrowsExceptionIfNumberOfDownloadsIsInvalid($numberOfDownloads)
    {
        $this->expectException(\Magento\Framework\Exception\InputException::class);
        $this->expectExceptionMessage('Number of downloads must be a positive integer.');

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
        $this->domainValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $this->fileValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
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
     * @return \PHPUnit\Framework\MockObject\MockObject
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
        $linkMock->expects($this->any())->method('getNumberOfDownloads')->willReturn(
            $linkData['number_of_downloads']
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
            $linkMock->expects($this->any())->method('getSampleType')->willReturn(
                $linkData['sample_type']
            );
        }
        if (isset($linkData['link_file_content'])) {
            $linkMock->expects($this->any())->method('getLinkFileContent')->willReturn($linkData['link_file_content']);
        }
        if (isset($linkData['sample_file_content'])) {
            $linkMock->expects($this->any())->method('getSampleFileContent')
                ->willReturn($linkData['sample_file_content']);
        }
        $linkMock->expects($this->any())->method('getSampleFile')->willReturn($this->sampleFileMock);

        return $linkMock;
    }
}
