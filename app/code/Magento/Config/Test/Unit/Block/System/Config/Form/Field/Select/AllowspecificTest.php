<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field\Select;

use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\DataObject;

class AllowspecificTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Block\System\Config\Form\Field\Select\Allowspecific
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_formMock;

    protected function setUp()
    {
        $testHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $randomMock = $this->createMock(Random::class);
        $randomMock->method('getRandomString')->willReturn('some-rando-string');
        $secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $secureRendererMock->method('renderEventListenerAsTag')
            ->willReturnCallback(
                function (string $event, string $listener, string $selector): string {
                    return "<script>document.querySelector('{$selector}').{$event} = () => { {$listener} };</script>";
                }
            );
        $secureRendererMock->method('renderTag')
            ->willReturnCallback(
                function (string $tag, array $attributes, string $content): string {
                    $attributes = new DataObject($attributes);

                    return "<$tag {$attributes->serialize()}>$content</$tag>";
                }
            );
        $this->_object = $testHelper->getObject(
            \Magento\Config\Block\System\Config\Form\Field\Select\Allowspecific::class,
            [
                '_escaper' => $testHelper->getObject(\Magento\Framework\Escaper::class),
                'random' => $randomMock,
                'secureRenderer' => $secureRendererMock
            ]
        );
        $this->_object->setData('html_id', 'spec_element');
        $this->_formMock = $this->createPartialMock(
            \Magento\Framework\Data\Form::class,
            ['getHtmlIdPrefix', 'getHtmlIdSuffix', 'getElement']
        );
    }

    public function testGetAfterElementHtml()
    {
        $this->_formMock->expects(
            $this->once()
        )->method(
            'getHtmlIdPrefix'
        )->will(
            $this->returnValue('test_prefix_')
        );
        $this->_formMock->expects(
            $this->once()
        )->method(
            'getHtmlIdSuffix'
        )->will(
            $this->returnValue('_test_suffix')
        );

        $afterHtmlCode = 'after html';
        $this->_object->setData('after_element_html', $afterHtmlCode);
        $this->_object->setForm($this->_formMock);

        $actual = $this->_object->getAfterElementHtml();

        $this->assertStringEndsWith('</script>' . $afterHtmlCode, $actual);
        $this->assertStringStartsWith('<script >', trim($actual));
        $this->assertContains('test_prefix_spec_element_test_suffix', $actual);
    }

    /**
     * @param $value
     * @dataProvider getHtmlWhenValueIsEmptyDataProvider
     */
    public function testGetHtmlWhenValueIsEmpty($value)
    {
        $this->_object->setForm($this->_formMock);

        $elementMock = $this->createPartialMock(\Magento\Framework\Data\Form\Element\Select::class, ['setDisabled']);

        $elementMock->expects($this->once())->method('setDisabled')->with('disabled');
        $countryId = 'tetst_county_specificcountry';
        $this->_object->setId('tetst_county_allowspecific');
        $this->_formMock->expects(
            $this->once()
        )->method(
            'getElement'
        )->with(
            $countryId
        )->will(
            $this->returnValue($elementMock)
        );

        $this->_object->setValue($value);
        $this->assertNotEmpty($this->_object->getHtml());
    }

    /**
     * @return array
     */
    public function getHtmlWhenValueIsEmptyDataProvider()
    {
        return [
            'zero' => ['1' => 0],
            'null' => ['1' => null],
            'false' => ['1' => false],
            'negative' => ['1' => -1]
        ];
    }
}
