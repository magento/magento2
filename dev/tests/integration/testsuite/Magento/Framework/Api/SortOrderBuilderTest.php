<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test of building the Data Object
 */
class SortOrderBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SortOrderBuilder
     */
    private $interceptedBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->interceptedBuilder = Bootstrap::getObjectManager()->get(SortOrderBuilder::class . '\Interceptor');
    }

    /**
     * Test Builder successfully creates object when Interceptor instance is provided.
     *
     * @return void
     */
    public function testCreate(): void
    {
        $this->assertEquals(SortOrder::class, get_class($this->interceptedBuilder->create()));
    }
}
