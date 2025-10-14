<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model\File;

use Magento\Downloadable\Api\Data\File\ContentInterface;
use Magento\Downloadable\Model\File\ContentValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContentValidatorTest extends TestCase
{
    /**
     * @var ContentValidator
     */
    protected $validator;

    /**
     * @var MockObject
     */
    protected $fileContentMock;

    protected function setUp(): void
    {
        $this->validator = new ContentValidator();

        $this->fileContentMock = $this->getMockForAbstractClass(ContentInterface::class);
    }

    public function testIsValid()
    {
        $this->fileContentMock->expects($this->any())->method('getFileData')
            ->willReturn(base64_encode('test content'));
        $this->fileContentMock->expects($this->any())->method('getName')
            ->willReturn('valid_name');

        $this->assertTrue($this->validator->isValid($this->fileContentMock));
    }

    public function testIsValidThrowsExceptionIfProvidedContentIsNotBase64Encoded()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('Provided content must be valid base64 encoded data.');
        $this->fileContentMock->expects($this->any())->method('getFileData')
            ->willReturn('not_a_base64_encoded_content');
        $this->fileContentMock->expects($this->any())->method('getName')
            ->willReturn('valid_name');
        $this->assertTrue($this->validator->isValid($this->fileContentMock));
    }

    /**
     * @dataProvider getInvalidNames
     * @param string $fileName
     */
    public function testIsValidThrowsExceptionIfProvidedImageNameContainsForbiddenCharacters($fileName)
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('Provided file name contains forbidden characters.');
        $this->fileContentMock->expects($this->any())->method('getFileData')
            ->willReturn(base64_encode('test content'));
        $this->fileContentMock->expects($this->any())->method('getName')
            ->willReturn($fileName);
        $this->assertTrue($this->validator->isValid($this->fileContentMock));
    }

    /**
     * @return array
     */
    public static function getInvalidNames()
    {
        return [
            ['test\test'],
            ['test/test'],
            ['test:test'],
            ['test"test'],
            ['test*test'],
            ['test;test'],
            ['test?test'],
            ['test{test'],
            ['test}test'],
            ['test|test'],
            ['test(test'],
            ['test)test'],
            ['test<test'],
            ['test>test'],
        ];
    }
}
