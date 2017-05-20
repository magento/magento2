<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Test\Unit\Model;

use \Magento\Inventory\Model\Source;
use \Magento\Inventory\Model\SourceCarrierLink;

class SourceCarrierLinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * constants for testing value purpose
     */
    const TEST_STRING = 'Jxecqi_Ahuytudxkruh';
    const TEST_POSITION = 10;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var SourceCarrierLink
     */
    protected $sourceCarrierLink;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->sourceCarrierLink = $this->objectManager->getObject(SourceCarrierLink::class);
    }


    public function testCarrierCode()
    {
        $this->sourceCarrierLink->setCarrierCode(SourceCarrierLinkTest::TEST_STRING);
        $this->assertEquals($this->source->getCarrierCode(), SourceCarrierLinkTest::TEST_STRING);
    }

    public function testPosition()
    {
        $this->sourceCarrierLink->setPosition(SourceCarrierLinkTest::TEST_POSITION);
        $this->assertEquals($this->source->getName(), SourceCarrierLinkTest::TEST_POSITION);
    }
}