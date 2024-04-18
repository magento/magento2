<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit;

use Magento\Framework\DataObject;
use Magento\Framework\Filter\DirectiveProcessor\DependDirective;
use Magento\Framework\Filter\DirectiveProcessor\IfDirective;
use Magento\Framework\Filter\DirectiveProcessor\LegacyDirective;
use Magento\Framework\Filter\DirectiveProcessor\TemplateDirective;
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

    /**
     * @var \Magento\Framework\Filter\Template\SignatureProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $signatureProvider;

    /**
     * @var \Magento\Framework\Filter\Template\FilteringDepthMeter|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $filteringDepthMeter;

    /**
     * @var array
     */
    private $listClasses = [
        DependDirective::class,
        IfDirective::class,
        TemplateDirective::class,
        LegacyDirective::class
    ];

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $objects = [];
        foreach ($this->listClasses as $className) {
            $classMock = $this->getMockBuilder($className)
                ->disableOriginalConstructor()
                ->onlyMethods([])
                ->getMock();
            $objects[] = [$className,$classMock];
        }
        $objectManager->prepareObjectManager($objects);

        $this->store = $objectManager->getObject(Store::class);

        $this->signatureProvider = $this->createPartialMock(
            \Magento\Framework\Filter\Template\SignatureProvider::class,
            ['get']
        );

        $this->signatureProvider->expects($this->any())
            ->method('get')
            ->willReturn('Z0FFbeCU2R8bsVGJuTdkXyiiZBzsaceV');

        $this->filteringDepthMeter = $this->createPartialMock(
            \Magento\Framework\Filter\Template\FilteringDepthMeter::class,
            ['showMark']
        );

        $this->templateFilter = $objectManager->getObject(
            \Magento\Framework\Filter\Template::class,
            [
                'signatureProvider' => $this->signatureProvider,
                'filteringDepthMeter' => $this->filteringDepthMeter
            ]
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

        $this->filteringDepthMeter->expects($this->any())
            ->method('showMark')
            ->willReturn(1);

        // Build arbitrary object to pass into the addAfterFilterCallback method
        $callbackObject = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['afterFilterCallbackMethod'])
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

        $this->filteringDepthMeter->expects($this->any())
            ->method('showMark')
            ->willReturn(1);

        // Build arbitrary object to pass into the addAfterFilterCallback method
        $callbackObject = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['afterFilterCallbackMethod'])
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
