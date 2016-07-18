<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DataObject\Test\Unit;

use Ramsey\Uuid\Uuid;

class IdentityServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DataObject\IdentityService
     */
    private $identityService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $ramseyFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $uuidMock;

    protected function setUp()
    {
        $this->ramseyFactoryMock = $this->getMock(\Ramsey\Uuid\UuidFactory::class, [], [], '', false);
        $this->uuidMock = $this->getMock(\Ramsey\Uuid\Uuid::class, [], [], '', false);
        $this->identityService = new \Magento\Framework\DataObject\IdentityService(
            $this->ramseyFactoryMock
        );
    }

    public function testGenerateId()
    {
        $this->ramseyFactoryMock->expects($this->once())->method('uuid4')->willReturn($this->uuidMock);
        $this->uuidMock->expects($this->once())->method('toString')->willReturn('string');
        $this->assertEquals('string', $this->identityService->generateId());
    }

    public function testGenerateIdForData()
    {
        $this->ramseyFactoryMock
            ->expects($this->once())
            ->method('uuid3')
            ->with(Uuid::NAMESPACE_DNS, 'name')
            ->willReturn($this->uuidMock);
        $this->uuidMock->expects($this->once())->method('toString')->willReturn('string');
        $this->assertEquals('string', $this->identityService->generateIdForData('name'));
    }
}
