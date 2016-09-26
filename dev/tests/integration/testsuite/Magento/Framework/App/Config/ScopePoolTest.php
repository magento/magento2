<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Cache\Frontend\Pool;
use \Magento\Framework\App\Config\ScopePool;

class ScopePoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ScopePool
     */
    private $scopePool;

    protected function setUp()
    {
        /** @var \Magento\TestFramework\ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->removeSharedInstance(ScopePool::class);
        $this->scopePool = $objectManager->get(ScopePool::class);
    }

    /**
     * @param string $scopeType
     * @param string $scopeCode
     * @dataProvider getScopeDataProvider
     */
    public function testGetScope($scopeType, $scopeCode = null)
    {
        $this->scopePool->clean();
        $this->assertEquals(
            $this->scopePool->getScope($scopeType, $scopeCode),
            $this->scopePool->getScope($scopeType, $scopeCode)
        );
    }

    public function getScopeDataProvider()
    {
        return [
            ['default'],
            ['stores', 'default'],
            ['websites', 'default']
        ];
    }
}
