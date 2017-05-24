<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model\Link;

use Magento\Downloadable\Model\Link\ContentValidator;

class ContentValidatorTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->fileValidatorMock = $this->getMock(
            \Magento\Downloadable\Model\File\ContentValidator::class,
            [],
            [],
            '',
            false
        );
        $this->urlValidatorMock = $this->getMock(
            \Magento\Framework\Url\Validator::class,
            [],
            [],
            '',
            false
        );
        $this->linkFileMock = $this->getMock(\Magento\Downloadable\Api\Data\File\ContentInterface::class);
        $this->sampleFileMock = $this->getMock(\Magento\Downloadable\Api\Data\File\ContentInterface::class);
        $this->validator = new ContentValidator($this->fileValidatorMock, $this->urlValidatorMock);
    }

    public function testIsValid()
    {
        $linkFileContentMock = $this->getMock(\Magento\Downloadable\Api\Data\File\ContentInterface::class);
        $sampleFileContentMock = $this->getMock(\Magento\Downloadable\Api\Data\File\ContentInterface::class);
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
        $this->fileValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
        $this->urlValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
        $linkMock = $this->getLinkMock($linkData);
        $this->assertTrue($this->validator->isValid($linkMock));
    }

    public function testIsValidSkipLinkContent()
    {
        $sampleFileContentMock = $this->getMock(\Magento\Downloadable\Api\Data\File\ContentInterface::class);
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
        $this->fileValidatorMock->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $this->urlValidatorMock->expects($this->never())->method('isValid')->will($this->returnValue(true));
        $linkMock = $this->getLinkMock($linkData);
        $this->assertTrue($this->validator->isValid($linkMock, false));
    }

    public function testIsValidSkipSampleContent()
    {
        $sampleFileContentMock = $this->getMock(\Magento\Downloadable\Api\Data\File\ContentInterface::class);
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
        $this->fileValidatorMock->expects($this->never())->method('isValid')->will($this->returnValue(true));
        $this->urlValidatorMock->expects($this->once())->method('isValid')->will($this->returnValue(true));
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
        $this->fileValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
        $this->urlValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
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
        $this->fileValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
        $this->urlValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
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
        $this->urlValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
        $this->fileValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
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
        $linkMock = $this->getMock(\Magento\Downloadable\Api\Data\LinkInterface::class);
        $linkMock->expects($this->any())->method('getTitle')->will($this->returnValue(
            $linkData['title']
        ));
        $linkMock->expects($this->any())->method('getPrice')->will($this->returnValue(
            $linkData['price']
        ));
        $linkMock->expects($this->any())->method('getSortOrder')->will($this->returnValue(
            $linkData['sort_order']
        ));
        $linkMock->expects($this->any())->method('isShareable')->will($this->returnValue(
            $linkData['shareable']
        ));
        $linkMock->expects($this->any())->method('getNumberOfDownloads')->will($this->returnValue(
            $linkData['number_of_downloads']
        ));
        $linkMock->expects($this->any())->method('getLinkType')->will($this->returnValue(
            $linkData['link_type']
        ));
        $linkMock->expects($this->any())->method('getLinkFile')->will($this->returnValue(
            $this->linkFileMock
        ));
        if (isset($linkData['link_url'])) {
            $linkMock->expects($this->any())->method('getLinkUrl')->will($this->returnValue(
                $linkData['link_url']
            ));
        }
        if (isset($linkData['sample_url'])) {
            $linkMock->expects($this->any())->method('getSampleUrl')->will($this->returnValue(
                $linkData['sample_url']
            ));
        }
        if (isset($linkData['sample_type'])) {
            $linkMock->expects($this->any())->method('getSampleType')->will($this->returnValue(
                $linkData['sample_type']
            ));
        }
        if (isset($linkData['link_file_content'])) {
            $linkMock->expects($this->any())->method('getLinkFileContent')->willReturn($linkData['link_file_content']);
        }
        if (isset($linkData['sample_file_content'])) {
            $linkMock->expects($this->any())->method('getSampleFileContent')
                ->willReturn($linkData['sample_file_content']);
        }
        $linkMock->expects($this->any())->method('getSampleFile')->will($this->returnValue(
            $this->sampleFileMock
        ));
        return $linkMock;
    }
}
