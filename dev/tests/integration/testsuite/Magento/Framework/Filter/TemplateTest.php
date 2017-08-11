<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filter;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Template
     */
    private $templateFilter;

    protected function setUp()
    {
        $this->templateFilter = \Magento\TestFramework\ObjectManager::getInstance()->create(Template::class);
    }

    /**
     * @param array $results
     * @param array $values
     * @dataProvider getFilterForDataProvider
     */
    public function testFilterFor($results, $values)
    {
        $this->templateFilter->setVariables(['order' => $this->getOrder(), 'things' => $this->getThings()]);
        $this->assertEquals($results, $this->invokeMethod($this->templateFilter, 'filterFor', [$values]));
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    private function getOrder()
    {
        $order = new \Magento\Framework\DataObject();
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

</ul>
EXPECTED_RESULT;

        $template2 = <<<TEMPLATE
<ul>
    {{for item in order.all_visible_items}}
    <li>
        index: {{var loop.index}} sku: {{var item.sku}} name: {{var item.name}} price: {{var item.price}} quantity: {{var item.ordered_qty}}
    </li>
    {{/for}}
</ul>
TEMPLATE;

        $expectedResult2 = <<<EXPECTED_RESULT
<ul>
    
    <li>
        index: 0 sku: ABC123 name: Product ABC price: 123 quantity: 2
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

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object
     * @param string $methodName
     * @param array  $parameters
     *
     * @return mixed Method return.
     */
    private function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
