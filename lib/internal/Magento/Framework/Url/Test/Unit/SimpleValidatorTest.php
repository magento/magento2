<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url\Test\Unit;

use Magento\Framework\Url\SimpleValidator;

class SimpleValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SimpleValidator
     */
    private $validator;

    protected function setUp()
    {
        $this->validator = new SimpleValidator();
    }

    /**
     * @param array $allowedSchemes
     * @param string $url
     * @param bool $expectedResult
     * @dataProvider isValidDataProvider
     */
    public function testIsValid(array $allowedSchemes, $url, $expectedResult)
    {
        $this->validator->setAllowedSchemes($allowedSchemes);
        $this->assertSame($expectedResult, $this->validator->isValid($url));
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            [
                'allowedSchemes' => [],
                'url' => 'http://example.com',
                'expectedResult' => true,
            ],
            [
                'allowedSchemes' => [],
                'url' => 'https://example.com',
                'expectedResult' => true,
            ],
            [
                'allowedSchemes' => [],
                'url' => 'http://example.com_test',
                'expectedResult' => false,
            ],
            [
                'allowedSchemes' => [],
                'url' => 'ftp://example.com',
                'expectedResult' => false,
            ],
            [
                'allowedSchemes' => ['ftp'],
                'url' => 'ftp://example.com',
                'expectedResult' => true,
            ],
            [
                'allowedSchemes' => ['ftp'],
                'url' => 'ftp://example.com_test',
                'expectedResult' => true,
            ],
        ];
    }
}
