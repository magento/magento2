<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model\File;

use Magento\Downloadable\Model\File\ContentValidator;

class ContentValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContentValidator
     */
    protected $validator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileContentMock;

    protected function setUp(): void
    {
        $this->validator = new \Magento\Downloadable\Model\File\ContentValidator();

        $this->fileContentMock = $this->createMock(\Magento\Downloadable\Api\Data\File\ContentInterface::class);
    }

    public function testIsValid()
    {
        $this->fileContentMock->expects($this->any())->method('getFileData')
            ->willReturn(base64_encode('test content'));
        $this->fileContentMock->expects($this->any())->method('getName')
            ->willReturn('valid_name');

        $this->assertTrue($this->validator->isValid($this->fileContentMock));
    }

    /**
     */
    public function testIsValidThrowsExceptionIfProvidedContentIsNotBase64Encoded()
    {
        $this->expectException(\Magento\Framework\Exception\InputException::class);
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
        $this->expectException(\Magento\Framework\Exception\InputException::class);
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
    public function getInvalidNames()
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
