<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field\Select;

use Magento\Config\Block\System\Config\Form\Field\Select\Allowspecific;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\DataObject;

class AllowspecificTest extends TestCase
{
    /**
     * @var Allowspecific
     */
    protected $_object;

    /**
     * @var MockObject
     */
    protected $_formMock;

    protected function setUp(): void
    {
        $testHelper = new ObjectManager($this);
        $objects = [
            [
                SecureHtmlRenderer::class,
                $this->createMock(SecureHtmlRenderer::class)
            ],
            [
                Random::class,
                $this->createMock(Random::class)
            ]
        ];
        $testHelper->prepareObjectManager($objects);
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
            Allowspecific::class,
            [
                '_escaper' => $testHelper->getObject(Escaper::class),
                'random' => $randomMock,
                'secureRenderer' => $secureRendererMock
            ]
        );
        $this->_object->setId('spec_element');
        $this->_formMock = $this->getMockBuilder(Form::class)
            ->addMethods(['getHtmlIdPrefix', 'getHtmlIdSuffix'])
            ->onlyMethods(['getElement'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetAfterElementHtml()
    {
        $this->_formMock->expects(
            $this->once()
        )->method(
            'getHtmlIdPrefix'
        )->willReturn(
            'test_prefix_'
        );
        $this->_formMock->expects(
            $this->once()
        )->method(
            'getHtmlIdSuffix'
        )->willReturn(
            '_test_suffix'
        );

        $afterHtmlCode = 'after html';
        $this->_object->setData('after_element_html', $afterHtmlCode);
        $this->_object->setForm($this->_formMock);
        $this->_object->setId('spec_element');

        $actual = $this->_object->getAfterElementHtml();

        $this->assertStringEndsWith('</script>' . $afterHtmlCode, $actual);
        $this->assertStringStartsWith('<script >', trim($actual));
        $this->assertStringContainsString('test_prefix_spec_element_test_suffix', $actual);
    }

    /**
     * @param $value
     * @dataProvider getHtmlWhenValueIsEmptyDataProvider
     */
    public function testGetHtmlWhenValueIsEmpty($value)
    {
        $this->_object->setForm($this->_formMock);

        $elementMock = $this->getMockBuilder(Select::class)
            ->addMethods(['setDisabled'])
            ->disableOriginalConstructor()
            ->getMock();

        $elementMock->expects($this->once())->method('setDisabled')->with('disabled');
        $countryId = 'tetst_county_specificcountry';
        $this->_object->setId('tetst_county_allowspecific');
        $this->_formMock->expects(
            $this->once()
        )->method(
            'getElement'
        )->with(
            $countryId
        )->willReturn(
            $elementMock
        );

        $this->_object->setValue($value);
        $this->assertNotEmpty($this->_object->getHtml());
    }

    /**
     * @return array
     */
    public static function getHtmlWhenValueIsEmptyDataProvider()
    {
        return [
            'zero' => ['1' => 0],
            'null' => ['1' => null],
            'false' => ['1' => false],
            'negative' => ['1' => -1]
        ];
    }
}
