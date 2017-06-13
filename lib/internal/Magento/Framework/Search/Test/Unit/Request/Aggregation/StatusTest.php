<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Test\Unit\Request\Aggregation;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Search\Request\Aggregation\Status */
    private $status;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    protected function setUp()
    {
        
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->status = $this->objectManagerHelper->getObject(
            \Magento\Framework\Search\Request\Aggregation\Status::class
        );
    }

    public function testIsEnabled()
    {
        $this->assertFalse($this->status->isEnabled());
    }
}
