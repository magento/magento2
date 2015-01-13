<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\View\Url;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\View\Url\Config
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    protected function setUp()
    {
        $this->_scopeConfig = $this->getMockBuilder(
            'Magento\Framework\App\Config\ScopeConfigInterface'
        )->disableOriginalConstructor()->getMock();
        $this->_model = new \Magento\Core\Model\View\Url\Config($this->_scopeConfig);
    }

    /**
     * @param $path
     * @param $expectedValue
     *
     * @dataProvider getConfigDataProvider
     */
    public function testGetValue($path, $expectedValue)
    {
        $this->_scopeConfig->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            $path
        )->will(
            $this->returnValue($expectedValue)
        );
        $actual = $this->_model->getValue($path);
        $this->assertEquals($expectedValue, $actual);
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [
            ['some/valid/path1', 'someValue'],
            ['some/valid/path2', 2],
            ['some/valid/path3', false],
            ['some/invalid/path3', null]
        ];
    }
}
