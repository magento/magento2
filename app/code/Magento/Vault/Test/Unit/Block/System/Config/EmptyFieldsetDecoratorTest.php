<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Block\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Vault\Block\System\Config\EmptyFieldsetDecorator;

class EmptyFieldsetDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function testRenderEmptyChildrenCollection()
    {
        $abstractElement = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $elementsCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $abstractElement->expects(static::once())
            ->method('getElements')
            ->willReturn($elementsCollection);

        $elementsCollection->expects(static::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));

        $om = new ObjectManager($this);
        /** @var EmptyFieldsetDecorator $emptySelectDecorator */
        $emptySelectDecorator = $om->getObject(EmptyFieldsetDecorator::class);

        static::assertEmpty($emptySelectDecorator->render($abstractElement));
    }

    public function testRenderChildrenCollectionWithoutView()
    {
        $abstractElement = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $elementsCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionItem = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $abstractElement->expects(static::once())
            ->method('getElements')
            ->willReturn($elementsCollection);

        $elementsCollection->expects(static::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$collectionItem]));

        $collectionItem->expects(static::once())
            ->method('toHtml')
            ->willReturn('');

        $om = new ObjectManager($this);
        /** @var EmptyFieldsetDecorator $emptySelectDecorator */
        $emptySelectDecorator = $om->getObject(EmptyFieldsetDecorator::class);

        static::assertEmpty($emptySelectDecorator->render($abstractElement));
    }
}
