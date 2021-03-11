<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Marketplace\Test\Unit\Block;

class PartnersTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Marketplace\Block\Partners
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
     * @return \PHPUnit\Framework\MockObject\MockObject|\Magento\Marketplace\Block\Partners
     */
    public function getPartnersBlockMock($methods = null)
    {
        return $this->createPartialMock(\Magento\Marketplace\Block\Partners::class, $methods);
    }

    /**
     * Gets partners model mock
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Magento\Marketplace\Model\Partners
     */
    public function getPartnersModelMock($methods)
    {
        return $this->createPartialMock(\Magento\Marketplace\Model\Partners::class, $methods);
    }
}
