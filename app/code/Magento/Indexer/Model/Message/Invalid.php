<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Model\Message;

/**
 * Message about invalid indexers.
 */
class Invalid implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var \Magento\Indexer\Model\Indexer\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param \Magento\Indexer\Model\Indexer\Collection $collection
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Indexer\Model\Indexer\Collection $collection,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->collection = $collection;
        $this->urlBuilder = $urlBuilder;
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
        // phpcs:ignore Magento2.Security.InsecureFunction
        return md5('INDEX_INVALID');
    }

    /**
     * Retrieve message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        $url = $this->urlBuilder->getUrl('indexer/indexer/list');
        //@codingStandardsIgnoreStart
        return __(
            'One or more <a href="%1">indexers are invalid</a>. Make sure your <a href="%2" target="_blank">Magento cron job</a> is running.',
            $url,
            'https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/cli/configure-cron-jobs.html#create-or-remove-the-magento-crontab'
        );
        //@codingStandardsIgnoreEnd
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
