<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\Message;

/**
 * System message about not filed required root category for store group
 */
class EmptyGroupCategory implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * Store group collection.
     *
     * @var \Magento\Store\Model\ResourceModel\Group\Collection
     */
    private $collection;

    /**
     * URL builder.
     *
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @param \Magento\Store\Model\ResourceModel\Group\Collection $collection Store group collection
     * @param \Magento\Framework\UrlInterface $urlBuilder URL builder
     */
    public function __construct(
        \Magento\Store\Model\ResourceModel\Group\Collection $collection,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->collection = $collection;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Check whether all store groups has assigned root category
     *
     * @return bool - true if at least one group does not have category
     */
    public function isDisplayed()
    {
        return !empty($this->collection->setWithoutAssignedCategoryFilter()->getItems());
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return 'empty_assigned_group_category';
    }

    /**
     * Retrieve message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        $url = $this->urlBuilder->getUrl('adminhtml/system_store');
        return __('One or more <a href="%1">stores</a> do not have assigned root category', $url);
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
}
