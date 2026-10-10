<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit;

use Magento\Framework\Validator\Url as UrlValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class UrlTest extends TestCase
{
    /**
     * @var UrlValidator
     */
    private $validator;

    protected function setUp(): void
    {
        $this->validator = new UrlValidator();
    }

    /**
     * @param array $allowedSchemes
     * @param string $url
     * @param bool $expectedResult     */
    #[DataProvider('isValidDataProvider')]
    public function testIsValid(array $allowedSchemes, $url, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->validator->isValid($url, $allowedSchemes));
    }

    /**
     * @return array
     */
    public static function isValidDataProvider()
    {
        return [
            [
                'allowedSchemes' => [],
                'url' => 'http://example.com',
                'expectedResult' => true,
            ],
            [
                'allowedSchemes' => ['http'],
                'url' => 'http://example.com',
                'expectedResult' => true,
            ],
            [
                'allowedSchemes' => [],
                'url' => 'https://example.com',
                'expectedResult' => true,
            ],
            [
                'allowedSchemes' => ['https'],
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
                'expectedResult' => true,
            ],
            [
                'allowedSchemes' => ['ftp'],
                'url' => 'ftp://example.com',
                'expectedResult' => true,
            ],
        ];
    }
}
