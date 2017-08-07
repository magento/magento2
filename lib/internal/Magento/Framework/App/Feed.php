<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

class Feed implements \Magento\Framework\App\FeedInterface
{
    /**
     * @var \Zend_Feed_Abstract
     */
    private $feed;

    /**
     * @param \Zend_Feed_Abstract $feed
     */
    public function __construct(\Zend_Feed_Abstract $feed)
    {
        $this->feed = $feed;
    }

    /**
     * Get the xml from Zend_Feed_Abstract object
     *
     * @return string
     */
    public function getFormatedContentAs(
        $format = FeedOutputFormatsInterface::DEFAULT_FORMAT
    ) {
        if ($format === FeedOutputFormatsInterface::DEFAULT_FORMAT) {
            return $this->feed->saveXml();
        }
        throw new \Magento\Framework\Exception\RuntimeException(
            __('Given feed format is not supported'),
            $e
        );
    }
}
