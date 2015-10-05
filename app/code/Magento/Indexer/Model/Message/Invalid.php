<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Model\Message;

class Invalid implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var \Magento\Indexer\Model\Indexer\Collection
     */
    protected $collection;

    /**
     * @param \Magento\Indexer\Model\Indexer\Collection $collection
     */
    public function __construct(
        \Magento\Indexer\Model\Indexer\Collection $collection
    ) {
        $this->collection = $collection;
    }

    /**
     * Check whether all indices are valid or not
     *
     * @return bool
     */
    public function isDisplayed()
    {
        /** @var \Magento\Indexer\Model\Indexer $indexer */
        foreach ($this->collection->getItems() as $indexer) {
            if ($indexer->getStatus() == \Magento\Framework\Indexer\StateInterface::STATUS_INVALID) {
                return true;
            }
        }

        return false;
    }

    //@codeCoverageIgnoreStart
    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return md5('INDEX_INVALID');
    }

    /**
     * Retrieve message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        // @codingStandardsIgnoreStart
        return __('One or more of the indexers are not valid. Please add Magento cron file to crontab or launch cron.php manually.');
        // @codingStandardsIgnoreEnd
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }
    //@codeCoverageIgnoreEnd
}
