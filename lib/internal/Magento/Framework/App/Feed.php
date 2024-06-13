<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

use Laminas\Feed\Writer\FeedFactory;

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
     * Feed constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->feeds = $data;
    }

    /**
     * @inheritDoc
     */
    public function getFormattedContent() : string
    {
        return FeedFactory::factory($this->feeds)->export(FeedFactoryInterface::FORMAT_RSS);
    }
}
