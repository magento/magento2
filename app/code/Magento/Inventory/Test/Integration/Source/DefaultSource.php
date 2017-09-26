<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Test\Integration\Source;

use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Inventory\Model\Source;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class DefaultSource
 * @package Magento\Inventory\Test\Integration\Source
 */
class DefaultSource extends TestCase
{
    /**
     * @var Source
     */
    private $source;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->source = Bootstrap::getObjectManager()->create(SourceRepositoryInterface::class);
        $this->source = $this->source->get(1);
    }

    /**
     * Test is default source wxist in DB
     */
    public function testDefaultSourceExist()
    {
        self::assertEquals(1, $this->source->getSourceId());
        self::assertEquals('Default Source', $this->source->getName());
    }

}