<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Review\Model\Review;
use Magento\Review\Model\Rating;

class RatingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Review\Model\Rating
     */
    private $rating;

    /**
     * Init objects needed by tests
     */
    protected function setUp()
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
