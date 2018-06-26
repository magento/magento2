<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Block\Adminhtml\Store;

class SwitcherPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SwitcherPlugin
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Paypal\Block\Adminhtml\Store\SwitcherPlugin();
    }

    /**
     * @param null|string $countryParam
     * @param array $getUrlParams
     * @dataProvider aroundGetUrlDataProvider
     */
    public function testAroundGetUrl($countryParam, $getUrlParams)
    {
        $subjectRequest = $this->getMockForAbstractClass('Magento\Framework\App\RequestInterface');
        $subjectRequest->expects($this->once())
            ->method('getParam')
            ->with(\Magento\Paypal\Model\Config\StructurePlugin::REQUEST_PARAM_COUNTRY)
            ->will($this->returnValue($countryParam));
        $subject = $this->getMock('Magento\Backend\Block\Store\Switcher', ['getRequest'], [], '', false);
        $subject->expects($this->any())->method('getRequest')->will($this->returnValue($subjectRequest));
        $getUrl = function ($route, $params) {
            return [$route, $params];
        };
        $this->assertEquals(['', $getUrlParams], $this->_model->aroundGetUrl($subject, $getUrl, '', []));
    }

    /**
     * @return array
     */
    public function aroundGetUrlDataProvider()
    {
        return [
            ['any value', [\Magento\Paypal\Model\Config\StructurePlugin::REQUEST_PARAM_COUNTRY => null]],
            [null, []]
        ];
    }
}
