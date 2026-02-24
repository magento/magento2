<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Block;

use Magento\Cms\Block\Block;
use Magento\Cms\Model\Block as CmsBlock;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class BlockTest extends TestCase
{
    /**
     * @var Block
     */
    protected $block;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->block = $objectManager->getObject(Block::class);
    }

    protected function tearDown(): void
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $id = 1;
        $this->block->setBlockId($id);
        $this->assertEquals([CmsBlock::CACHE_TAG . '_' . $id], $this->block->getIdentities());
    }
}
