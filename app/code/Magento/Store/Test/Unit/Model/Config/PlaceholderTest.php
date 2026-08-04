<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\Config;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Config\Placeholder;
use Magento\Store\Model\Store;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PlaceholderTest extends TestCase
{
    /**
     * @var Placeholder
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    protected function setUp(): void
    {
        $this->_requestMock = $this->createMock(Http::class);
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getDistroBaseUrl'
        )->willReturn(
            'http://localhost/'
        );
        $this->_model = new Placeholder(
            $this->_requestMock,
            [
                'unsecureBaseUrl' => Store::XML_PATH_UNSECURE_BASE_URL,
                'secureBaseUrl' => Store::XML_PATH_SECURE_BASE_URL
            ],
            Store::BASE_URL_PLACEHOLDER
        );
    }

    public function testProcess()
    {
        $data = [
            'web' => [
                'unsecure' => [
                    'base_url' => 'http://localhost/',
                    'base_link_url' => '{{unsecure_base_url}}website/de',
                ],
                'secure' => [
                    'base_url' => 'https://localhost/',
                    'base_link_url' => '{{secure_base_url}}website/de',
                ],
            ],
            'path' => 'value',
            'some_url' => '{{base_url}}some',
            'level1' => [
                'level2' => [
                    'level3' => [
                        // test that all levels are processed (i.e. implementation is not hardcoded to 3 levels)
                        'level4' => '{{secure_base_url}}level4'
                    ]
                ]
            ]
        ];
        $expectedResult = $data;
        $expectedResult['web']['unsecure']['base_link_url'] = 'http://localhost/website/de';
        $expectedResult['web']['secure']['base_link_url'] = 'https://localhost/website/de';
        $expectedResult['level1']['level2']['level3']['level4'] = 'https://localhost/level4';
        $expectedResult['some_url'] = 'http://localhost/some';
        $this->assertEquals($expectedResult, $this->_model->process($data));
    }

    public function testProcessEmptyArray()
    {
        $data = [];
        $expectedResult = [];
        $this->assertEquals($expectedResult, $this->_model->process($data));
    }

    /**
     * @param mixed $secureBaseUrl
     */
    #[DataProvider('emptySecureBaseUrlDataProvider')]
    public function testProcessThrowsWhenSecureBaseUrlIsEmpty($secureBaseUrl): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            'Cannot resolve "{{secure_base_url}}" because "web/secure/base_url" is empty.'
        );

        $this->_model->process([
            'web' => [
                'unsecure' => [
                    'base_url' => 'http://localhost/',
                ],
                'secure' => [
                    'base_url' => $secureBaseUrl,
                    'base_link_url' => '{{secure_base_url}}website/de',
                ],
            ],
        ]);
    }

    /**
     * @return array
     */
    public static function emptySecureBaseUrlDataProvider(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
        ];
    }

    /**
     * @param mixed $unsecureBaseUrl
     */
    #[DataProvider('emptyUnsecureBaseUrlDataProvider')]
    public function testProcessThrowsWhenUnsecureBaseUrlIsEmpty($unsecureBaseUrl): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            'Cannot resolve "{{unsecure_base_url}}" because "web/unsecure/base_url" is empty.'
        );

        $this->_model->process([
            'web' => [
                'unsecure' => [
                    'base_url' => $unsecureBaseUrl,
                    'base_link_url' => '{{unsecure_base_url}}website/de',
                ],
                'secure' => [
                    'base_url' => 'https://localhost/',
                ],
            ],
        ]);
    }

    /**
     * @return array
     */
    public static function emptyUnsecureBaseUrlDataProvider(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
        ];
    }

    public function testProcessThrowsWhenSecureBaseUrlPathIsMissing(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            'Cannot resolve "{{secure_base_url}}" because "web/secure/base_url" is empty.'
        );

        $this->_model->process([
            'web' => [
                'unsecure' => [
                    'base_url' => 'http://localhost/',
                ],
                'secure' => [
                    'base_link_url' => '{{secure_base_url}}website/de',
                ],
            ],
        ]);
    }
}
