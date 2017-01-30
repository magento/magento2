<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config;

use Magento\Store\Model\Store;

class PlaceholderTest extends \PHPUnit_Framework_TestCase
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
        $this->_requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
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
        ];
        $expectedResult = $data;
        $expectedResult['web']['unsecure']['base_link_url'] = 'http://localhost/website/de';
        $expectedResult['web']['secure']['base_link_url'] = 'https://localhost/website/de';
        $expectedResult['some_url'] = 'http://localhost/some';
        $this->assertEquals($expectedResult, $this->_model->process($data));
    }
}
