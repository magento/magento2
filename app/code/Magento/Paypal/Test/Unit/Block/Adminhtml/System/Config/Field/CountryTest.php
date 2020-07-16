<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config\Field;

use Magento\Backend\Model\Url;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\Js;
use Magento\Paypal\Block\Adminhtml\System\Config\Field\Country;
use Magento\Paypal\Model\Config\StructurePlugin;
use PHPUnit\Framework\Constraint\LogicalAnd;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CountryTest extends TestCase
{
    /**
     * @var Country
     */
    protected $_model;

    /**
     * @var AbstractElement
     */
    protected $_element;

    /**
     * @var RequestInterface|MockObject
     */
    protected $_request;

    /**
     * @var Js|MockObject
     */
    protected $_jsHelper;

    /**
     * @var Url|MockObject
     */
    protected $_url;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->_element = $this->getMockForAbstractClass(
            AbstractElement::class,
            [],
            '',
            false,
            true,
            true,
            ['getHtmlId', 'getElementHtml', 'getName']
        );
        $this->_element->expects($this->any())
            ->method('getHtmlId')
            ->willReturn('html id');
        $this->_element->expects($this->any())
            ->method('getElementHtml')
            ->willReturn('element html');
        $this->_element->expects($this->any())
            ->method('getName')
            ->willReturn('name');
        $this->_request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->_jsHelper = $this->createMock(Js::class);
        $this->_url = $this->createMock(Url::class);
        $this->_model = $helper->getObject(
            Country::class,
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
            ->willReturnCallback(function ($param) use ($requestCountry, $requestDefaultCountry) {
                if ($param == StructurePlugin::REQUEST_PARAM_COUNTRY) {
                    return $requestCountry;
                }
                if ($param == Country::REQUEST_PARAM_DEFAULT_COUNTRY) {
                    return $requestDefaultCountry;
                }
                return $param;
            });
        $this->_element->setInherit($inherit);
        $this->_element->setCanUseDefaultValue($canUseDefault);
        $constraints = [
            new StringContains('document.observe("dom:loaded", function() {'),
            new StringContains(
                '$("' . $this->_element->getHtmlId() . '").observe("change", function () {'
            ),
        ];
        if ($canUseDefault && ($requestCountry == 'US') && $requestDefaultCountry) {
            $constraints[] = new StringContains(
                '$("' . $this->_element->getHtmlId() . '_inherit").observe("click", function () {'
            );
        }
        $this->_jsHelper->expects($this->once())
            ->method('getScript')
            ->with(new LogicalAnd($constraints));
        $this->_url->expects($this->once())
            ->method('getUrl')
            ->with(
                '*/*/*',
                [
                    'section' => 'section',
                    'website' => 'website',
                    'store' => 'store',
                    StructurePlugin::REQUEST_PARAM_COUNTRY => '__country__'
                ]
            );
        $this->_model->render($this->_element);
    }

    /**
     * @return array
     */
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
