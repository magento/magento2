<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            array(),
            array(),
            '',
            false
        );
        $this->urlValidatorMock = $this->getMock(
            '\Magento\Framework\Url\Validator',
            array(),
            array(),
            '',
            false
        );
        $this->sampleFileMock = $this->getMock(
            '\Magento\Downloadable\Service\V1\Data\FileContent',
            array(),
            array(),
            '',
            false
        );
        $this->validator = new DownloadableSampleContentValidator($this->fileValidatorMock, $this->urlValidatorMock);
    }

    public function testIsValid()
    {
        $sampleContentData = array(
            'title' => 'Title',
            'sort_order' => 1,
            'sample_type' => 'file',
        );
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
        $sampleContentData = array(
            'title' => 'Title',
            'sort_order' => $sortOrder,
            'sample_type' => 'file',
        );
        $this->fileValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
        $this->urlValidatorMock->expects($this->any())->method('isValid')->will($this->returnValue(true));
        $this->validator->isValid($this->getSampleContentMock($sampleContentData));
    }

    /**
     * @return array
     */
    public function getInvalidSortOrder()
    {
        return array(
            array(-1),
            array(1.1),
            array('string'),
        );
    }

    /**
     * @param array $sampleContentData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSampleContentMock(array $sampleContentData)
    {
        $contentMock = $this->getMock(
            '\Magento\Downloadable\Service\V1\DownloadableSample\Data\DownloadableSampleContent',
            array(),
            array(),
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
