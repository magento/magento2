<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryConfiguration\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SourceItemConfiguration extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\InventoryConfiguration\Model\SourceItemConfiguration */
    private $sourceItemConfigurationModel;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /** @var int  */
    private $testSourceItemId = 5;

    /** @var int  */
    private $testSecondSourceItemId = 9;

    /**
     * Setup the needed objects.
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->sourceItemConfigurationModel = $this->objectManagerHelper->getObject(
            \Magento\InventoryConfiguration\Model\SourceItemConfiguration::class
        );
    }

    /**
     * Test get source item id from object.
     */
    public function testGetSourceItemId()
    {
        $this->sourceItemConfigurationModel->setData('source_item_id', $this->testSourceItemId);

        $this->assertEquals($this->sourceItemConfigurationModel->getSourceItemId(), $this->testSourceItemId);
    }

    /**
     * Test set source item id in object.
     */
    public function testSetSourceId()
    {
        $this->sourceItemConfigurationModel->setSourceItemId($this->testSourceItemId);

        $this->assertEquals($this->sourceItemConfigurationModel->getData('source_item_id'), $this->testSourceItemId);
    }

    /**
     * Test overwrite the source item id.
     */
    public function testSetSourceIdAgainWithNoChange()
    {
        $this->sourceItemConfigurationModel->setSourceItemId($this->testSourceItemId);
        $this->sourceItemConfigurationModel->setSourceItemId($this->testSecondSourceItemId);

        $this->assertEquals($this->sourceItemConfigurationModel->getData('source_item_id'), $this->testSourceItemId);
    }
}
