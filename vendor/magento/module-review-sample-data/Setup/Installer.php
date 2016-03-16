<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReviewSampleData\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var \Magento\ReviewSampleData\Model\Review
     */
    protected $review;

    /**
     * @param \Magento\ReviewSampleData\Model\Review $review
     */
    public function __construct(\Magento\ReviewSampleData\Model\Review $review)
    {
        $this->review = $review;
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        $this->review->install(['Magento_ReviewSampleData::fixtures/products_reviews.csv']);
    }
}