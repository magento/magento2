<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\Config\Source;

/**
 * Class BlockTest
 */
class BlockTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Cms\Model\Config\Source\Block
     */
    protected $block;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->collectionFactory = $this->createPartialMock(
            \Magento\Cms\Model\ResourceModel\Block\CollectionFactory::class,
            ['create']
        );

        $this->block = $objectManager->getObject(
            \Magento\Cms\Model\Config\Source\Block::class,
            [
                'collectionFactory' => $this->collectionFactory,
            ]
        );
    }

    /**
     * Run test toOptionArray method
     *
     * @return void
     */
    public function testToOptionArray()
    {
        $blockCollectionMock = $this->createMock(\Magento\Cms\Model\ResourceModel\Block\Collection::class);

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($blockCollectionMock);

        $blockCollectionMock->expects($this->once())
            ->method('toOptionIdArray')
            ->willReturn('return-value');

        $this->assertEquals('return-value', $this->block->toOptionArray());
    }
}
