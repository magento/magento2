<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ups\Model;

class CarrierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Ups\Model\Carrier
     */
    private $carrier;

    protected function setUp()
    {
        $this->carrier = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Ups\Model\Carrier'
        );
    }

    public function testGetShipAcceptUrl()
    {
        $this->assertEquals('https://wwwcie.ups.com/ups.app/xml/ShipAccept', $this->carrier->getShipAcceptUrl());
    }

    /**
     * Test ship accept url for live site
     *
     * @magentoConfigFixture current_store carriers/ups/is_account_live 1
     */
    public function testGetShipAcceptUrlLive()
    {
        $this->assertEquals('https://onlinetools.ups.com/ups.app/xml/ShipAccept', $this->carrier->getShipAcceptUrl());
    }

    public function testGetShipConfirmUrl()
    {
        $this->assertEquals('https://wwwcie.ups.com/ups.app/xml/ShipConfirm', $this->carrier->getShipConfirmUrl());
    }

    /**
     * Test ship accept url for live site
     *
     * @magentoConfigFixture current_store carriers/ups/is_account_live 1
     */
    public function testGetShipConfirmUrlLive()
    {
        $this->assertEquals(
            'https://onlinetools.ups.com/ups.app/xml/ShipConfirm',
            $this->carrier->getShipConfirmUrl()
        );
    }
}
