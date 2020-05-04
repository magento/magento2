<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Request\Aggregation;

use Magento\Framework\Search\Request\Aggregation\Status;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    /** @var Status */
    private $status;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->status = $this->objectManagerHelper->getObject(
            Status::class
        );
    }

    public function testIsEnabled()
    {
        $this->assertFalse($this->status->isEnabled());
    }
}
