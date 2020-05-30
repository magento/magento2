<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\Config;

use Magento\Framework\App\Request\Http;
use Magento\Store\Model\Config\Processor\Placeholder as PlaceholderProcessor;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PlaceholderTest extends TestCase
{
    /**
     * @var PlaceholderProcessor
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
        $this->_model = new \Magento\Store\Model\Config\Placeholder(
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
}
