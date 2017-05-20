<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Test\Unit\Model;

use \Magento\Inventory\Model\Source;

class SourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * constants for testing value purpose
     */
    const TEST_ID = 1;
    const TEST_STRING = 'Jxecqi_Ahuytudxkruh';
    const TEST_IS_ACTIVE = 1;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var Source
     */
    protected $source;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->source = $this->objectManager->getObject(Source::class);
    }


    public function testSourceId()
    {
        $this->source->setSourceId(SourceTest::TEST_ID);
        $this->assertEquals($this->source->getSourceId(), SourceTest::TEST_ID);
    }

    public function testName()
    {
        $this->source->setName(SourceTest::TEST_STRING);
        $this->assertEquals($this->source->getName(), SourceTest::TEST_STRING);
    }

    public function testEmail()
    {
        $this->source->setEmail(SourceTest::TEST_STRING);
        $this->assertEquals($this->source->getEmail(), SourceTest::TEST_STRING);
    }

    public function testContactName()
    {
        $this->source->setContactName(SourceTest::TEST_STRING);
        $this->assertEquals($this->source->getContactName(), SourceTest::TEST_STRING);
    }

    public function testIsActive()
    {
        $this->source->setIsActive(SourceTest::TEST_IS_ACTIVE);
        $this->assertEquals($this->source->getIsActive(), SourceTest::TEST_IS_ACTIVE);
    }

    public function testDescription()
    {
        $this->source->setDescription(SourceTest::TEST_STRING);
        $this->assertEquals($this->source->getDescription(), SourceTest::TEST_STRING);
    }

    public function testLatitude()
    {
        $this->source->setLatitude(SourceTest::TEST_STRING);
        $this->assertEquals($this->source->getLatitude(), SourceTest::TEST_STRING);
    }

    public function testCountryId()
    {
        $this->source->setCountryId(SourceTest::TEST_ID);
        $this->assertEquals($this->source->getCountryId(), SourceTest::TEST_ID);
    }

    public function testRegion()
    {
        $this->source->setRegion(SourceTest::TEST_STRING);
        $this->assertEquals($this->source->getRegion(), SourceTest::TEST_STRING);
    }

    public function testStreet()
    {
        $this->source->setStreet(SourceTest::TEST_STRING);
        $this->assertEquals($this->source->getStreet(), SourceTest::TEST_STRING);
    }

    public function testPhone()
    {
        $this->source->setPhone(SourceTest::TEST_STRING);
        $this->assertEquals($this->source->getPhone(), SourceTest::TEST_STRING);
    }

    public function testFax()
    {
        $this->source->setFax(SourceTest::TEST_STRING);
        $this->assertEquals($this->source->getFax(), SourceTest::TEST_STRING);
    }

    public function testPriority()
    {
        $this->source->setPriority(SourceTest::TEST_STRING);
        $this->assertEquals($this->source->getPriority(), SourceTest::TEST_STRING);
    }

    public function testCarrierLinks()
    {
        $carrierLink1 = $this->getMock(
            \Magento\Inventory\Model\SourceCarrierLink::class,
            [],
            [],
            '',
            false
        );

        $carrierLink2 = $this->getMock(
            \Magento\Inventory\Model\SourceCarrierLink::class,
            [],
            [],
            '',
            false
        );

        $carrierLinks = [$carrierLink1, $carrierLink2];

        $this->source->setCarrierLinks($carrierLinks);
        $this->assertEquals($carrierLinks, $this->source->getCarrierLinks());
    }

}
