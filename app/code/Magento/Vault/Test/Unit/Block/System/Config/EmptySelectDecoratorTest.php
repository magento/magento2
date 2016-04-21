<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Vault\Block\System\Config\EmptySelectDecorator;

class EmptySelectDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function testRenderEmptySelect()
    {
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $emptySelectDecorator = new EmptySelectDecorator($contextMock);

        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock->expects(static::once())
            ->method('getData')
            ->with('values')
            ->willReturn(null);
        static::assertEmpty($emptySelectDecorator->render($selectMock));

        $abstractElement = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->getMock();
        static::assertEmpty($emptySelectDecorator->render($abstractElement));
    }
}
