<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Block\Adminhtml\Store;

class SwitcherPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SwitcherPlugin
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new SwitcherPlugin();
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

    public function aroundGetUrlDataProvider()
    {
        return [
            ['any value', [\Magento\Paypal\Model\Config\StructurePlugin::REQUEST_PARAM_COUNTRY => null]],
            [null, []]
        ];
    }
}
