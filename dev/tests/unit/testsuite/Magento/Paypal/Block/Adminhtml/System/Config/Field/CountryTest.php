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
namespace Magento\Paypal\Block\Adminhtml\System\Config\Field;

class CountryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Country
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected $_element;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var \Magento\Framework\View\Helper\Js|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_jsHelper;

    /**
     * @var \Magento\Backend\Model\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_url;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_element = $this->getMockForAbstractClass(
            'Magento\Framework\Data\Form\Element\AbstractElement',
            [],
            '',
            false,
            true,
            true,
            ['getHtmlId', 'getElementHtml', 'getName']
        );
        $this->_element->expects($this->any())
            ->method('getHtmlId')
            ->will($this->returnValue('html id'));
        $this->_element->expects($this->any())
            ->method('getElementHtml')
            ->will($this->returnValue('element html'));
        $this->_element->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('name'));
        $this->_request = $this->getMockForAbstractClass('Magento\Framework\App\RequestInterface');
        $this->_jsHelper = $this->getMock('Magento\Framework\View\Helper\Js', [], [], '', false);
        $this->_url = $this->getMock('Magento\Backend\Model\Url', [], [], '', false);
        $this->_model = $helper->getObject(
            'Magento\Paypal\Block\Adminhtml\System\Config\Field\Country',
            ['request' => $this->_request, 'jsHelper' => $this->_jsHelper, 'url' => $this->_url]
        );
    }

    /**
     * @param null|string $requestCountry
     * @param null|string $requestDefaultCountry
     * @param bool $canUseDefault
     * @param bool $inherit
     * @dataProvider renderDataProvider
     */
    public function testRender($requestCountry, $requestDefaultCountry, $canUseDefault, $inherit)
    {
        $this->_request->expects($this->any())
            ->method('getParam')
            ->will($this->returnCallback(function ($param) use ($requestCountry, $requestDefaultCountry) {
                if ($param == \Magento\Paypal\Model\Config\StructurePlugin::REQUEST_PARAM_COUNTRY) {
                    return $requestCountry;
                }
                if ($param == Country::REQUEST_PARAM_DEFAULT_COUNTRY) {
                    return $requestDefaultCountry;
                }
                return $param;
            }));
        $this->_element->setInherit($inherit);
        $this->_element->setCanUseDefaultValue($canUseDefault);
        $constraints = [
            new \PHPUnit_Framework_Constraint_StringContains('document.observe("dom:loaded", function() {'),
            new \PHPUnit_Framework_Constraint_StringContains(
                '$("' . $this->_element->getHtmlId() . '").observe("change", function () {'
            )
        ];
        if ($canUseDefault && ($requestCountry == 'US') && $requestDefaultCountry) {
            $constraints[] = new \PHPUnit_Framework_Constraint_StringContains(
                '$("' . $this->_element->getHtmlId() . '_inherit").observe("click", function () {'
            );
        }
        $this->_jsHelper->expects($this->once())
            ->method('getScript')
            ->with(new \PHPUnit_Framework_Constraint_And($constraints));
        $this->_url->expects($this->once())
            ->method('getUrl')
            ->with(
                '*/*/*',
                [
                    'section' => 'section',
                    'website' => 'website',
                    'store' => 'store',
                    \Magento\Paypal\Model\Config\StructurePlugin::REQUEST_PARAM_COUNTRY => '__country__'
                ]
            );
        $this->_model->render($this->_element);
    }

    public function renderDataProvider()
    {
        return [
            [null, null, false, false],
            [null, null, true, true],
            [null, null, true, false],
            ['IT', null, true, false],
            ['IT', null, true, true],
            ['IT', 'GB', true, false],
            ['US', 'GB', true, true],
            ['US', 'GB', true, false],
            ['US', null, true, false],
        ];
    }
}
