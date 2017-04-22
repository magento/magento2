<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

class Feed implements \Magento\Framework\App\FeedInterface
{
    /**
     * @var \Magento\Framework\App\FeedImporterInterface
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
    public function asXml()
    {
        return $this->feed->saveXml();
    }
}
