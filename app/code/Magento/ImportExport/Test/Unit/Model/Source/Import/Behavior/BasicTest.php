<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\ImportExport\Model\Source\Import\Behavior\Basic
 */
namespace Magento\ImportExport\Test\Unit\Model\Source\Import\Behavior;

use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Source\Import\Behavior\Basic;
use Magento\ImportExport\Test\Unit\Model\Source\Import\AbstractBehaviorTestCase;

class BasicTest extends AbstractBehaviorTestCase
{
    /**
     * Expected behavior group code
     *
     * @var string
     */
    protected $_expectedCode = 'basic';

    /**
     * Expected behaviours
     *
     * @var array
     */
    protected $_expectedBehaviors = [
        Import::BEHAVIOR_APPEND,
        Import::BEHAVIOR_REPLACE,
        Import::BEHAVIOR_DELETE,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->_model = new Basic();
    }

    /**
     * Test toArray method
     *
     * @covers \Magento\ImportExport\Model\Source\Import\Behavior\Basic::toArray
     */
    public function testToArray()
    {
        $behaviorData = $this->_model->toArray();
        $this->assertIsArray($behaviorData);
        $this->assertEquals($this->_expectedBehaviors, array_keys($behaviorData));
    }

    /**
     * Test behavior group code
     *
     * @covers \Magento\ImportExport\Model\Source\Import\Behavior\Basic::getCode
     */
    public function testGetCode()
    {
        $this->assertEquals($this->_expectedCode, $this->_model->getCode());
    }
}
