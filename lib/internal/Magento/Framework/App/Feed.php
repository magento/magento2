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
     * @var \Zend_Feed
     */
    private $feedProcessor;

    /**
     * @var array
     */
    private $data;

    /**
     * @param Zend_Feed $feedProcessor
     * @param array $data
     */
    public function __construct(
        \Zend_Feed $feedProcessor,
        array $data
    ) {
        $this->feedProcessor = $feedProcessor;
        $this->data = $data;
    }

    /**
     * Returns the formatted feed content
     *
     * @param string $format
     *
     * @return string
     */
    public function getFormattedContentAs(
        $format = self::FORMAT_XML
    ) {
        $feed = $this->feedProcessor::importArray(
            $this->data,
            FeedFactoryInterface::FORMAT_RSS
        );
        return $feed->saveXml();
    }
}
