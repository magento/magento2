<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Zend\Feed\Writer\FeedFactory;

/**
 * Default XML feed class
 */
class Feed implements FeedInterface
{
    /**
     * @var array
     */
    private $feeds;

    /**
     * @param array $feeds
     */
    public function __construct(
        array $feeds
    ) {
        $this->feeds = $feeds;
    }

    /**
     * Returns the formatted feed content
     *
     * @return string
     */
    public function getFormattedContent() 
    {
        return FeedFactory::factory($this->feeds)
            ->export(FeedFactoryInterface::FORMAT_RSS);
    }
}
