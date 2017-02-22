<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Config\Source;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class \Magento\Customer\Model\Config\Source\Group
 */
class GroupTest extends \PHPUnit_Framework_TestCase
{
    public function testToOptionArray()
    {
        /** @var Group $group */
        $group = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Config\Source\Group');
        $this->assertEquals(
            [
                ['value' => '', 'label' => '-- Please Select --'],
                ['value' => 1, 'label' => 'General'],
                ['value' => 2, 'label' => 'Wholesale'],
                ['value' => 3, 'label' => 'Retailer'],
            ],
            $group->toOptionArray()
        );
    }
}
