<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit;

use Magento\Framework\DataObject;
use Magento\Framework\Filter\Template;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

/**
 * Template Filter test.
 */
class TemplateTest extends TestCase
{
    /**
     * @var Template
     */
    private $templateFilter;

    /**
     * @var Store
     */
    private $store;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->templateFilter = $objectManager->getObject(Template::class);
        $this->store = $objectManager->getObject(Store::class);
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
            ->willReturn($expectedResult);

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
            ->willReturn($expectedResult);

        $this->templateFilter->addAfterFilterCallback([$callbackObject, 'afterFilterCallbackMethod']);

        // Callback should run and filter content
        $this->assertEquals($expectedResult, $this->templateFilter->filter($value));

        // Callback should *not* run as callbacks should be reset
        $this->assertEquals($value, $this->templateFilter->filter($value));
    }

    /**
     * @param $type
     * @return array
     */
    public function getTemplateAndExpectedResults($type)
    {
        switch ($type) {
            case 'noLoopTag':
                $template = $expected = '';
                break;
            case 'noBodyTag':
                $template = <<<TEMPLATE
<ul>
{{for item in order.all_visible_items}}{{/for}}
</ul>
TEMPLATE;
                $expected = <<<TEMPLATE
<ul>
{{for item in order.all_visible_items}}{{/for}}
</ul>
TEMPLATE;
                break;
            case 'noItemTag':
                $template = <<<TEMPLATE
<ul>
{{for in order.all_visible_items}}
    <li>
        {{var loop.index}} name: {{var thing.name}}, lastname: {{var thing.lastname}}, age: {{var thing.age}}
    </li>
{{/for}}
</ul>
TEMPLATE;
                $expected = <<<TEMPLATE
<ul>
{{for in order.all_visible_items}}
    <li>
         name: , lastname: , age: 
    </li>
{{/for}}
</ul>
TEMPLATE;
                break;
            case 'noItemNoBodyTag':
                $template = <<<TEMPLATE
<ul>
{{for in order.all_visible_items}}
    
{{/for}}
</ul>
TEMPLATE;
                $expected = <<<TEMPLATE
<ul>
{{for in order.all_visible_items}}
    
{{/for}}
</ul>
TEMPLATE;
                break;
            case 'noItemNoDataNoBodyTag':
                $template = <<<TEMPLATE
<ul>
{{for in }}
    
{{/for}}
</ul>
TEMPLATE;
                $expected = <<<TEMPLATE
<ul>
{{for in }}
    
{{/for}}
</ul>
TEMPLATE;
                break;
            default:
                $template = <<<TEMPLATE
<ul>
    {{for item in order.all_visible_items}}
    <li>
        index: {{var loop.index}} sku: {{var item.sku}}
        name: {{var item.name}} price: {{var item.price}} quantity: {{var item.ordered_qty}}
    </li>
    {{/for}}
</ul>
TEMPLATE;
                $expected = <<<TEMPLATE
<ul>
    
    <li>
        index: 0 sku: ABC123
        name: Product ABC price: 123 quantity: 2
    </li>
    
    <li>
        index: 1 sku: DOREMI
        name: Product DOREMI price: 456 quantity: 1
    </li>
    
</ul>
TEMPLATE;
        }
        return [$template, ['order' => $this->getObjectData()], $expected];
    }

    /**
     * @return object
     */
    private function getObjectData()
    {
        $objectManager = new ObjectManager($this);
        $dataObject = $objectManager->getObject(DataObject::class);

        /* $var @dataObject \Magento\Framework\DataObject */

        $visibleItems = [
            [
                'sku' => 'ABC123',
                'name' => 'Product ABC',
                'price' => '123',
                'ordered_qty' => '2'
            ],
            [
                'sku' => 'DOREMI',
                'name' => 'Product DOREMI',
                'price' => '456',
                'ordered_qty' => '1'
            ]
        ];

        $dataObject->setAllVisibleItems($visibleItems);
        return $dataObject;
    }
}
