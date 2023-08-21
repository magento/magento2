<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Group;

use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class ResolverTest extends TestCase
{
    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResolve()
    {
        $customerId = 1;
        $expectedGroupId = 1;

        $resolver = Bootstrap::getObjectManager()->create(Resolver::class);
        $groupId = $resolver->resolve($customerId);
        $this->assertEquals($groupId, $expectedGroupId);
    }
}
