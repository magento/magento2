<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Marketplace\Test\Unit\Block;

use Magento\Marketplace\Block\Partners;
use Magento\Marketplace\Model\Partners as PartnersModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PartnersTest extends TestCase
{
    /**
     * @var MockObject|Partners
     */
    private $partnersBlockMock;

    protected function setUp(): void
    {
        $this->partnersBlockMock = $this->getPartnersBlockMock(
            [
                'getPartnersModel'
            ]
        );
    }

    /**
     * @covers \Magento\Marketplace\Block\Partners::getPartners
     */
    public function testGetPartners()
    {
        $partnersModelMock = $this->getPartnersModelMock(['getPartners']);
        $partnersModelMock->expects($this->once())
            ->method('getPartners')
            ->willReturn([]);

        $this->partnersBlockMock->expects($this->once())
            ->method('getPartnersModel')
            ->willReturn($partnersModelMock);

        $this->partnersBlockMock->getPartners();
    }

    /**
     * Gets partners block mock
     *
     * @return MockObject|Partners
     */
    public function getPartnersBlockMock($methods = null)
    {
        return $this->createPartialMock(Partners::class, $methods);
    }

    /**
     * Gets partners model mock
     *
     * @return MockObject|PartnersModel
     */
    public function getPartnersModelMock($methods)
    {
        return $this->createPartialMock(PartnersModel::class, $methods);
    }
}
