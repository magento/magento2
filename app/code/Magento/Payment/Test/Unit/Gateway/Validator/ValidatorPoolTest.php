<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Validator;

use Magento\Payment\Gateway\Validator\ValidatorPool;

class ValidatorPoolTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $commandI = $this->getMockBuilder('Magento\Payment\Gateway\Validator\ValidatorInterface')
            ->getMockForAbstractClass();
        $tMap = $this->getMockBuilder('Magento\Framework\ObjectManager\TMap')
            ->disableOriginalConstructor()
            ->getMock();
        $tMap->expects(static::once())
            ->method('offsetExists')
            ->with('validator')
            ->willReturn(true);
        $tMap->expects(static::once())
            ->method('offsetGet')
            ->with('validator')
            ->willReturn($commandI);

        $pool = new ValidatorPool($tMap);

        static::assertSame($commandI, $pool->get('validator'));
    }

    public function testGetException()
    {
        $this->setExpectedException('Magento\Framework\Exception\NotFoundException');
        $tMap = $this->getMockBuilder('Magento\Framework\ObjectManager\TMap')
            ->disableOriginalConstructor()
            ->getMock();
        $tMap->expects(static::once())
            ->method('offsetExists')
            ->with('validator')
            ->willReturn(false);

        $pool = new ValidatorPool($tMap);
        $pool->get('validator');
    }
}
