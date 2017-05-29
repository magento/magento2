<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LayeredNavigation\Test\Unit\Model\Aggregation;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\LayeredNavigation\Model\Aggregation\Status */
    private $resolver;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->resolver = $this->objectManagerHelper->getObject(
            \Magento\LayeredNavigation\Model\Aggregation\Status::class
        );
    }

    public function testIsEnabled()
    {
        $this->assertTrue($this->resolver->isEnabled());
    }
}
