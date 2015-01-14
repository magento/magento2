<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\DownloadableSample\Data;

class DownloadableSampleContentValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DownloadableSampleContentValidator
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
        $this->sampleFileMock = $this->getMock(
            '\Magento\Downloadable\Service\V1\Data\FileContent',
            [],
            [],
            '',
            false
        );
        $this->validator = new DownloadableSampleContentValidator($this->fileValidatorMock, $this->urlValidatorMock);
    }

    public function testIsValid()
    {
        $sampleContentData = [
            'title' => 'Title',
            'sort_order' => 1,
            'sample_type' => 'file',
        ];
        $this->fileValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
        $this->urlValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
        $contentMock = $this->getSampleContentMock($sampleContentData);
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
        $sampleContentData = [
            'title' => 'Title',
            'sort_order' => $sortOrder,
            'sample_type' => 'file',
        ];
        $this->fileValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
        $this->urlValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
        $this->validator->isValid($this->getSampleContentMock($sampleContentData));
    }

    /**
     * @return array
     */
    public function getInvalidSortOrder()
    {
        return [
            [-1],
            [1.1],
            ['string'],
        ];
    }

    /**
     * @param array $sampleContentData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSampleContentMock(array $sampleContentData)
    {
        $contentMock = $this->getMock(
            '\Magento\Downloadable\Service\V1\DownloadableSample\Data\DownloadableSampleContent',
            [],
            [],
            '',
            false
        );
        $contentMock->expects($this->any())->method('getTitle')->will($this->returnValue(
            $sampleContentData['title']
        ));

        $contentMock->expects($this->any())->method('getSortOrder')->will($this->returnValue(
            $sampleContentData['sort_order']
        ));
        $contentMock->expects($this->any())->method('getSampleType')->will($this->returnValue(
            $sampleContentData['sample_type']
        ));
        if (isset($sampleContentData['sample_url'])) {
            $contentMock->expects($this->any())->method('getSampleUrl')->will($this->returnValue(
                $sampleContentData['sample_url']
            ));
        }
        $contentMock->expects($this->any())->method('getSampleFile')->will($this->returnValue(
            $this->sampleFileMock
        ));
        return $contentMock;
    }
}
