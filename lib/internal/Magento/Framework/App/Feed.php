<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\Phrase;
use Magento\Framework\Exception\InputException;

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
     * @param string $format
     * @return string
     * @throws InputException
     */
    public function getFormattedContentAs(string $format = FeedInterface::DEFAULT_FORMAT): string
    {
        if ($format === FeedInterface::DEFAULT_FORMAT) {
            return $this->feed->saveXml();
        }
        throw new InputException(
            new Phrase('Given feed format is not supported')
        );
    }
}
