<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Review\Model\Rating;
use Magento\Review\Model\Review;
use PHPUnit\Framework\TestCase;

class RatingTest extends TestCase
{
    /**
     * @var Rating
     */
    private $rating;

    /**
     * Init objects needed by tests
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->rating = $helper->getObject(Rating::class);
    }

    /**
     * @covers \Magento\Review\Model\Rating::getIdentities()
     * @return void
     */
    public function testGetIdentities()
    {
        static::assertEquals([Review::CACHE_TAG], $this->rating->getIdentities());
    }
}
