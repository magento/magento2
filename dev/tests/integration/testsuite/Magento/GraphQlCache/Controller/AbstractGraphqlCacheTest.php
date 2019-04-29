<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller;

use PHPUnit\Framework\TestCase;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Abstract test class for Graphql cache tests
 */
abstract class AbstractGraphqlCacheTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->usePageCachePlugin();
    }

    /**
     * Enable full page cache plugin
     */
    protected function usePageCachePlugin(): void
    {
        /** @var  $registry \Magento\Framework\Registry */
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $registry->register('use_page_cache_plugin', true, true);
    }
}
