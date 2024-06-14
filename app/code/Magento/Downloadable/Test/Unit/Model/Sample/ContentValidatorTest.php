<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model\Sample;

use Magento\Downloadable\Api\Data\File\ContentInterface;
use Magento\Downloadable\Api\Data\SampleInterface;
use Magento\Downloadable\Helper\File;
use Magento\Downloadable\Model\Sample\ContentValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Magento\Downloadable\Model\Sample\ContentValidator.
 */
class ContentValidatorTest extends TestCase
{
    /**
     * @var ContentValidator
     */
    protected $validator;

    /**
     * @var MockObject
     */
    protected $fileValidatorMock;

    /**
     * @var MockObject
     */
    protected $urlValidatorMock;

    /**
     * @var MockObject
     */
    protected $linkFileMock;

    /**
     * @var MockObject
     */
    protected $sampleFileMock;

    /**
     * @var File|MockObject
     */
    private $fileMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->fileValidatorMock = $this->createMock(\Magento\Downloadable\Model\File\ContentValidator::class);
        $this->urlValidatorMock = $this->createMock(Validator::class);
        $this->sampleFileMock = $this->getMockForAbstractClass(ContentInterface::class);
        $this->fileMock = $this->createMock(File::class);

        $this->validator = $objectManager->getObject(
            ContentValidator::class,
            [
                'fileContentValidator' => $this->fileValidatorMock,
                'urlValidator' => $this->urlValidatorMock,
                'fileHelper' => $this->fileMock,
            ]
        );
    }

    public function testIsValid()
    {
        $sampleFileContentMock = $this->getMockForAbstractClass(ContentInterface::class);
        $sampleContentData = [
            'title' => 'Title',
            'sort_order' => 1,
            'sample_type' => 'file',
            'sample_file_content' => $sampleFileContentMock,
        ];
        $this->fileValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $this->urlValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $contentMock = $this->getSampleContentMock($sampleContentData);
        $this->assertTrue($this->validator->isValid($contentMock));
    }

    /**
     * @param string|int|float $sortOrder
     * @dataProvider getInvalidSortOrder
     */
    public function testIsValidThrowsExceptionIfSortOrderIsInvalid($sortOrder)
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('Sort order must be a positive integer.');
        $sampleContentData = [
            'title' => 'Title',
            'sort_order' => $sortOrder,
            'sample_type' => 'file',
        ];
        $this->fileValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $this->urlValidatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $this->validator->isValid($this->getSampleContentMock($sampleContentData));
    }

    /**
     * @return array
     */
    public static function getInvalidSortOrder()
    {
        return [
            [-1],
            [1.1],
            ['string'],
        ];
    }

    /**
     * @param array $sampleContentData
     * @return MockObject
     */
    protected function getSampleContentMock(array $sampleContentData)
    {
        $contentMock = $this->getMockForAbstractClass(SampleInterface::class);
        $contentMock->expects($this->any())->method('getTitle')->willReturn(
            $sampleContentData['title']
        );

        $contentMock->expects($this->any())->method('getSortOrder')->willReturn(
            $sampleContentData['sort_order']
        );
        $contentMock->expects($this->any())->method('getSampleType')->willReturn(
            $sampleContentData['sample_type']
        );
        if (isset($sampleContentData['sample_url'])) {
            $contentMock->expects($this->any())->method('getSampleUrl')->willReturn(
                $sampleContentData['sample_url']
            );
        }
        if (isset($sampleContentData['sample_file_content'])) {
            $contentMock->expects($this->any())->method('getSampleFileContent')
                ->willReturn($sampleContentData['sample_file_content']);
        }
        $contentMock->expects($this->any())->method('getSampleFile')->willReturn(
            $this->sampleFileMock
        );

        return $contentMock;
    }
}
