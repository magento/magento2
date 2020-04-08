<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config;

use Magento\Store\Model\Store;

class PlaceholderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\Config\Processor\Placeholder
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    protected function setUp()
    {
        $this->_requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getDistroBaseUrl'
        )->will(
            $this->returnValue('http://localhost/')
        );
        $this->_model = new \Magento\Store\Model\Config\Placeholder(
            $this->_requestMock,
            [
                'unsecureBaseUrl' => Store::XML_PATH_UNSECURE_BASE_URL,
                'secureBaseUrl' => Store::XML_PATH_SECURE_BASE_URL
            ],
            \Magento\Store\Model\Store::BASE_URL_PLACEHOLDER
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
