<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Block;

class BlockTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Block\Block
     */
    protected $block;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(\Magento\Cms\Block\Block::class);
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $id = 1;
        $this->block->setBlockId($id);
        $this->assertEquals([\Magento\Cms\Model\Block::CACHE_TAG . '_' . $id], $this->block->getIdentities());
    }
}
