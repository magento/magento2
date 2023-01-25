<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

use Magento\Framework\DataObject;
use Magento\Store\Model\Store;
use Magento\TestFramework\ObjectManager;

class TemplateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Template
     */
    private $templateFilter;

    protected function setUp(): void
    {
        $this->templateFilter = ObjectManager::getInstance()->create(Template::class);
    }

    /**
     * @param array $results
     * @param array $value
     * @dataProvider getFilterForDataProvider
     */
    public function testFilterFor($results, $value)
    {
        $this->templateFilter->setVariables(['order' => $this->getOrder(), 'things' => $this->getThings()]);
        self::assertEquals($results, $this->templateFilter->filter($value));
    }

    /**
     * @return DataObject
     */
    private function getOrder()
    {
        $order = new DataObject();
        $visibleItems = [
            [
                'sku' => 'ABC123',
                'name' => 'Product ABC',
                'price' => '123',
                'ordered_qty' => '2'
            ]
        ];
        $order->setAllVisibleItems($visibleItems);
        return $order;
    }

    public function getThings()
    {
        return [
            ['name' => 'Richard', 'age' => 24],
            ['name' => 'Jane', 'age' => 12],
            ['name' => 'Spot', 'age' => 7],
            ['name' => 'Bill', 'age' => '25']
        ];
    }

    /**
     * @return array
     */
    public function getFilterForDataProvider()
    {
        $template = <<<TEMPLATE
<ul>
{{for thing in things}}
    <li>
        {{var loop.index}} name: {{var thing.name}}, lastname: {{var thing.lastname}}, age: {{var thing.age}}
    </li>
{{/for}}
</ul>
TEMPLATE;

        $expectedResult = <<<EXPECTED_RESULT
<ul>

    <li>
        0 name: Richard, lastname: , age: 24
    </li>

    <li>
        1 name: Jane, lastname: , age: 12
    </li>

    <li>
        2 name: Spot, lastname: , age: 7
    </li>

    <li>
        3 name: Bill, lastname: , age: 25
    </li>

</ul>
EXPECTED_RESULT;

        $template2 = <<<TEMPLATE
<ul>
{{for item in order.all_visible_items}}
    <li>
        index: {{var loop.index}} sku: {{var item.sku}}
        name: {{var item.name}} price: {{var item.price}} quantity: {{var item.ordered_qty}}
    </li>
{{/for}}
</ul>
TEMPLATE;

        $expectedResult2 = <<<EXPECTED_RESULT
<ul>

    <li>
        index: 0 sku: ABC123
        name: Product ABC price: 123 quantity: 2
    </li>

</ul>
EXPECTED_RESULT;
        return [
            [
                $expectedResult,
                $template
            ],
            [
                $expectedResult2,
                $template2
            ]
        ];
    }

    public function testDependDirective()
    {
        $this->templateFilter->setVariables(
            [
                'customer' => new DataObject(['name' => 'John Doe']),
            ]
        );

        $template = '{{depend customer.getName()}}foo{{/depend}}';
        $template .= '{{depend customer.getName()}}{{var customer.getName()}}{{/depend}}';
        $template .= '{{depend customer.getFoo()}}bar{{/depend}}';
        $expected = 'fooJohn Doe';
        self::assertEquals($expected, $this->templateFilter->filter($template));
    }

    public function testIfDirective()
    {
        $this->templateFilter->setVariables(
            [
                'customer' => new DataObject(['name' => 'John Doe']),
            ]
        );

        $template = '{{if customer.getName()}}foo{{/if}}{{if customer.getNope()}}not me{{else}}bar{{/if}}';
        $expected = 'foobar';
        self::assertEquals($expected, $this->templateFilter->filter($template));
    }

    public function testNonDataObjectVariableParsing()
    {
        $this->templateFilter->setVariables(
            [
                'address' => new class {
                    public function format($type)
                    {
                        return '<foo>' . $type . '</foo>';
                    }
                }
            ]
        );

        $template = '{{var address.format(\'html\')}}';
        $expected = '';
        self::assertEquals($expected, $this->templateFilter->filter($template));
    }

    public function testStrictModeByDefault()
    {
        $this->templateFilter->setVariables(
            [
                'address' => new class {
                    public function format()
                    {
                        throw new \Exception('Should not run');
                    }
                }
            ]
        );

        $template = '{{var address.format(\'html\')}}';
        self::assertEquals('', $this->templateFilter->filter($template));
    }

    public function testComplexVariableArguments()
    {
        $this->templateFilter->setVariables(
            [
                'address' => new class {
                    public function format($a, $b, $c)
                    {
                        return $a . ' ' . $b . ' ' . $c['param1'];
                    }
                },
                'arg1' => 'foo'
            ]
        );

        $template = '{{var address.format($arg1,\'bar\',[param1:baz])}}';
        $expected = '';

        self::assertEquals($expected, $this->templateFilter->filter($template));
    }

    public function testComplexVariableGetterArguments()
    {
        $this->templateFilter->setVariables(
            [
                'address' => new class extends DataObject {
                    public function getFoo($a, $b, $c)
                    {
                        return $a . ' ' . $b . ' ' . $c['param1'];
                    }
                },
                'arg1' => 'foo'
            ]
        );

        $template = '{{var address.getFoo($arg1,\'bar\',[param1:baz])}}';
        $expected = '';
        self::assertEquals($expected, $this->templateFilter->filter($template));
    }

    public function testNonDataObjectRendersBlankInStrictMode()
    {
        $this->templateFilter->setVariables(
            [
                'address' => new class {
                    public function format($type)
                    {
                        return '<foo>' . $type . '</foo>';
                    }
                },
            ]
        );

        $template = '{{var address.format(\'html\')}}';
        $expected = '';
        self::assertEquals($expected, $this->templateFilter->filter($template));
    }

    public function testDataObjectCanRenderPropertiesStrictMode()
    {
        $this->templateFilter->setVariables(
            [
                'customer' => new DataObject(['name' => 'John Doe']),
            ]
        );

        $template = '{{var customer.name}} - {{var customer.getName()}}';
        $expected = 'John Doe - John Doe';
        self::assertEquals($expected, $this->templateFilter->filter($template));
    }

    public function testScalarDataKeys()
    {
        $this->templateFilter->setVariables(
            [
                'customer_data' => [
                    'name' => 'John Doe',
                    'address' => [
                        'street' => ['easy'],
                        'zip' => new DataObject(['bar' => 'yay'])
                    ]
                ],
                'myint' => 123,
                'myfloat' => 1.23,
                'mystring' => 'abc',
                'mybool' => true,
                'myboolf' => false,
            ]
        );

        $template = '{{var customer_data.name}}'
        . ' {{var customer_data.address.street.0}}'
        . ' {{var customer_data.address.zip.bar}}'
        . ' {{var}}'
        . ' {{var myint}}'
        . ' {{var myfloat}}'
        . ' {{var mystring}}'
        . ' {{var mybool}}'
        . ' {{var myboolf}}';

        $expected = 'John Doe easy yay {{var}} 123 1.23 abc 1 ';
        self::assertEquals($expected, $this->templateFilter->filter($template));
    }

    public function testModifiers()
    {
        $this->templateFilter->setVariables(
            [
                'address' => '11501 Domain Dr.' . "\n" . 'Austin, TX 78758'
            ]
        );

        $template = '{{mydir "somevalue" param1=yes|foofilter|nl2br}}blah {{var address}} blah{{/mydir}}';

        $expected = 'HALB 85787 XT ,NITSUA<br />' . "\n" . '.RD NIAMOD 10511 HALBSEYEULAVEMOS';
        self::assertEquals($expected, $this->templateFilter->filter($template));
    }

    public function testDefaultModifiers()
    {
        $this->templateFilter->setVariables(
            [
                'address' => '11501 Domain Dr.' . "\n" . 'Austin, TX 78758'
            ]
        );

        $template = '{{mydir "somevalue" param1=yes}}blah {{var address}} blah{{/mydir}}';

        $expected = 'HALB 85787 XT ,NITSUA' . "\n" . '.RD NIAMOD 10511 HALBSEYEULAVEMOS';
        self::assertEquals($expected, $this->templateFilter->filter($template));
    }

    public function testFilterVarious1()
    {
        $this->templateFilter->setVariables(
            [
                'customer' => new DataObject(['firstname' => 'Felicia', 'lastname' => 'Henry']),
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
     * Check that if calling a method of an object fails expected result is returned.
     */
    public function testInvalidMethodCall()
    {
        $this->templateFilter->setVariables(['dateTime' => '\DateTime']);
        $this->assertEquals(
            '\DateTime',
            $this->templateFilter->filter('{{var dateTime.createFromFormat(\'d\',\'1548201468\')}}')
        );
    }
}
