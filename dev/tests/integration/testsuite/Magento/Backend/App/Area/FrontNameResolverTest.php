<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Backend\App\Area;

use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class FrontNameResolverTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var FrontNameResolver
     */
    protected $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(
            FrontNameResolver::class
        );
        $_SERVER['HTTP_HOST'] = 'localhost';
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store web/unsecure/base_url http://example.com/
     */
    public function testIsHostBackend()
    {
        $this->assertTrue($this->model->isHostBackend());
    }
}
