<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\DownloadableLink\Data;

class DownloadableLinkContentValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DownloadableLinkContentValidator
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
            '\Magento\Downloadable\Service\V1\Data\FileContentValidator',
            [],
            [],
            '',
            false
        );
        $this->urlValidatorMock = $this->getMock(
            '\Magento\Framework\Url\Validator',
            [],
            [],
            '',
            false
        );
        $this->linkFileMock = $this->getMock(
            '\Magento\Downloadable\Service\V1\Data\FileContent',
            [],
            [],
            '',
            false
        );
        $this->sampleFileMock = $this->getMock(
            '\Magento\Downloadable\Service\V1\Data\FileContent',
            [],
            [],
            '',
            false
        );
        $this->validator = new DownloadableLinkContentValidator($this->fileValidatorMock, $this->urlValidatorMock);
    }

    public function testIsValid()
    {
        $linkContentData = [
            'title' => 'Title',
            'sort_order' => 1,
            'price' => 10.1,
            'shareable' => true,
            'number_of_downloads' => 100,
            'link_type' => 'file',
            'sample_type' => 'file',
        ];
        $this->fileValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
        $this->urlValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
        $contentMock = $this->getLinkContentMock($linkContentData);
        $this->assertTrue($this->validator->isValid($contentMock));
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
        $contentMock = $this->getLinkContentMock($linkContentData);
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
        $contentMock = $this->getLinkContentMock($linkContentData);
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
        $contentMock = $this->getLinkContentMock($linkContentData);
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
     * @param array $linkContentData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLinkContentMock(array $linkContentData)
    {
        $contentMock = $this->getMock(
            '\Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableLinkContent',
            [],
            [],
            '',
            false
        );
        $contentMock->expects($this->any())->method('getTitle')->will($this->returnValue(
            $linkContentData['title']
        ));
        $contentMock->expects($this->any())->method('getPrice')->will($this->returnValue(
            $linkContentData['price']
        ));
        $contentMock->expects($this->any())->method('getSortOrder')->will($this->returnValue(
            $linkContentData['sort_order']
        ));
        $contentMock->expects($this->any())->method('isShareable')->will($this->returnValue(
            $linkContentData['shareable']
        ));
        $contentMock->expects($this->any())->method('getNumberOfDownloads')->will($this->returnValue(
            $linkContentData['number_of_downloads']
        ));
        $contentMock->expects($this->any())->method('getLinkType')->will($this->returnValue(
            $linkContentData['link_type']
        ));
        $contentMock->expects($this->any())->method('getLinkFile')->will($this->returnValue(
            $this->linkFileMock
        ));
        if (isset($linkContentData['link_url'])) {
            $contentMock->expects($this->any())->method('getLinkUrl')->will($this->returnValue(
                $linkContentData['link_url']
            ));
        }
        if (isset($linkContentData['sample_url'])) {
            $contentMock->expects($this->any())->method('getSampleUrl')->will($this->returnValue(
                $linkContentData['sample_url']
            ));
        }
        if (isset($linkContentData['sample_type'])) {
            $contentMock->expects($this->any())->method('getSampleType')->will($this->returnValue(
                $linkContentData['sample_type']
            ));
        }
        $contentMock->expects($this->any())->method('getSampleFile')->will($this->returnValue(
            $this->sampleFileMock
        ));
        return $contentMock;
    }
}
