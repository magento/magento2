<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filter\Test\Unit;

class TemplateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Filter\Template
     */
    private $templateFilter;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->templateFilter = $objectManager->getObject(\Magento\Framework\Filter\Template::class);
    }

    public function testFilter()
    {
        $this->templateFilter->setVariables(
            [
                'customer' => new \Magento\Framework\DataObject(['firstname' => 'Felicia', 'lastname' => 'Henry']),
                'company' => 'A. L. Price',
                'street1' => '687 Vernon Street',
                'city' => 'Parker Dam',
                'region' => 'CA',
                'postcode' => '92267',
                'telephone' => '760-663-5876',
            ]
        );

        $template = <<<TEMPLATE
{{var customer.firstname}} {{depend middlename}}{{var middlename}} {{/depend}}{{var customer.getLastname()}}
{{depend company}}{{var company}}{{/depend}}
{{if street1}}{{var street1}}
{{/if}}
{{depend street2}}{{var street2}}{{/depend}}
{{depend street3}}{{var street3}}{{/depend}}
{{depend street4}}{{var street4}}{{/depend}}
{{if city}}{{var city}},  {{/if}}{{if region}}{{var region}}, {{/if}}{{if postcode}}{{var postcode}}{{/if}}
{{var country}}
{{depend telephone}}T: {{var telephone}}{{/depend}}
{{depend fax}}F: {{var fax}}{{/depend}}
{{depend vat_id}}VAT: {{var vat_id}}{{/depend}}
TEMPLATE;

        $expectedResult = <<<EXPECTED_RESULT
Felicia Henry
A. L. Price
687 Vernon Street




Parker Dam,  CA, 92267

T: 760-663-5876


EXPECTED_RESULT;

        $this->assertEquals(
            $expectedResult,
            $this->templateFilter->filter($template),
            'Template was processed incorrectly'
        );
    }

    /**
     * @covers \Magento\Framework\Filter\Template::afterFilter
     * @covers \Magento\Framework\Filter\Template::addAfterFilterCallback
     */
    public function testAfterFilter()
    {
        $value = 'test string';
        $expectedResult = 'TEST STRING';

        // Build arbitrary object to pass into the addAfterFilterCallback method
        $callbackObject = $this->getMockBuilder('stdObject')
            ->setMethods(['afterFilterCallbackMethod'])
            ->getMock();

        $callbackObject->expects($this->once())
            ->method('afterFilterCallbackMethod')
            ->with($value)
            ->will($this->returnValue($expectedResult));

        // Add callback twice to ensure that the check in addAfterFilterCallback prevents the callback from being called
        // more than once
        $this->templateFilter->addAfterFilterCallback([$callbackObject, 'afterFilterCallbackMethod']);
        $this->templateFilter->addAfterFilterCallback([$callbackObject, 'afterFilterCallbackMethod']);

        $this->assertEquals($expectedResult, $this->templateFilter->filter($value));
    }

    /**
     * @covers \Magento\Framework\Filter\Template::afterFilter
     * @covers \Magento\Framework\Filter\Template::addAfterFilterCallback
     * @covers \Magento\Framework\Filter\Template::resetAfterFilterCallbacks
     */
    public function testAfterFilterCallbackReset()
    {
        $value = 'test string';
        $expectedResult = 'TEST STRING';

        // Build arbitrary object to pass into the addAfterFilterCallback method
        $callbackObject = $this->getMockBuilder('stdObject')
            ->setMethods(['afterFilterCallbackMethod'])
            ->getMock();

        $callbackObject->expects($this->once())
            ->method('afterFilterCallbackMethod')
            ->with($value)
            ->will($this->returnValue($expectedResult));

        $this->templateFilter->addAfterFilterCallback([$callbackObject, 'afterFilterCallbackMethod']);

        // Callback should run and filter content
        $this->assertEquals($expectedResult, $this->templateFilter->filter($value));

        // Callback should *not* run as callbacks should be reset
        $this->assertEquals($value, $this->templateFilter->filter($value));
    }

    /**
     * @covers \Magento\Framework\Filter\Template::varDirective
     * @covers \Magento\Framework\Filter\Template::getVariable
     * @covers \Magento\Framework\Filter\Template::getStackArgs
     * @dataProvider varDirectiveDataProvider
     */
    public function testVarDirective($construction, $variables, $expectedResult)
    {
        $this->templateFilter->setVariables($variables);
        $this->assertEquals($expectedResult, $this->templateFilter->filter($construction));
    }

    /**
     * @return array
     */
    public function varDirectiveDataProvider()
    {
        /* @var $dataObjectVariable \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject */
        $dataObjectVariable = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods(['bar'])
            ->getMock();
        $dataObjectVariable->expects($this->once())
            ->method('bar')
            ->willReturn('DataObject Method Return');

        /* @var $nonDataObjectVariable \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject */
        $nonDataObjectVariable = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $nonDataObjectVariable->expects($this->once())
            ->method('escapeHtml')
            ->willReturnArgument(0);

        return [
            'no variables' => [
                '{{var}}',
                [],
                '{{var}}',
            ],
            'invalid variable' => [
                '{{var invalid}}',
                ['foobar' => 'barfoo'],
                '',
            ],
            'string variable' => [
                '{{var foobar}}',
                ['foobar' => 'barfoo'],
                'barfoo',
            ],
            'array argument to method' => [
                '{{var foo.bar([param_1:value_1, param_2:$value_2, param_3:[a:$b, c:$d]])}}',
                [
                    'foo' => $dataObjectVariable,
                    'value_2' => 'lorem',
                    'b' => 'bee',
                    'd' => 'dee',
                ],
                'DataObject Method Return'
            ],
            'non DataObject method call' => [
                '{{var foo.escapeHtml($value)}}',
                [
                    'foo' => $nonDataObjectVariable,
                    'value' => 'lorem'
                ],
                'lorem'
            ],
            'non DataObject undefined method call' => [
                '{{var foo.undefinedMethod($value)}}',
                [
                    'foo' => $nonDataObjectVariable,
                    'value' => 'lorem'
                ],
                ''
            ],
        ];
    }
}
