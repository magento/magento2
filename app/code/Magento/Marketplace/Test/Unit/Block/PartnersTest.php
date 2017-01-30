<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Marketplace\Test\Unit\Block;

class PartnersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Marketplace\Block\Partners
     */
    private $partnersBlockMock;

    protected function setUp()
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
            ->will($this->returnValue([]));

        $this->partnersBlockMock->expects($this->once())
            ->method('getPartnersModel')
            ->will($this->returnValue($partnersModelMock));

        $this->partnersBlockMock->getPartners();
    }

    /**
     * Gets partners block mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Marketplace\Block\Partners
     */
    public function getPartnersBlockMock($methods = null)
    {
        return $this->getMock('Magento\Marketplace\Block\Partners', $methods, [], '', false);
    }

    /**
     * Gets partners model mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Marketplace\Model\Partners
     */
    public function getPartnersModelMock($methods)
    {
        return $this->getMock('Magento\Marketplace\Model\Partners', $methods, [], '', false);
    }
}
