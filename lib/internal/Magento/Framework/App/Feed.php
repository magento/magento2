<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Default XML feed class
 */
class Feed implements FeedInterface
{
    /**
     * @param Zend_Feed $feed
     * @param array $data
     */
    public function __construct(
        \Zend_Feed $feed,
        array $data
    ) {
       $this->feed = $feed;
       $this->data = $data;
    }

    /**
     * Returns the formatted feed content
     * 
     * @return string
     */
    public function getFormatedContentAs(
        $format = self::DEFAULT_FORMAT
    ) {
        $feed = $this->feed::importArray(
            $this->data, 
            FeedFactoryInterface::DEFAULT_FORMAT
        );
        return $this->feed->saveXml();
    }
}