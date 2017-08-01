<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\Message;

use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Model\ResourceModel\Group\Collection as GroupCollection;

/**
 * System message about not filled required root category for store group
 * @since 2.2.0
 */
class EmptyGroupCategory implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * Store group collection.
     *
     * @var GroupCollection
     * @since 2.2.0
     */
    private $collection;

    /**
     * URL builder.
     *
     * @var UrlInterface
     * @since 2.2.0
     */
    private $urlBuilder;

    /**
     * List of store groups with unassigned root categories.
     *
     * @var GroupInterface[]
     * @since 2.2.0
     */
    private $items = null;

    /**
     * @param GroupCollection $collection Store group collection
     * @param UrlInterface $urlBuilder URL builder
     * @since 2.2.0
     */
    public function __construct(
        GroupCollection $collection,
        UrlInterface $urlBuilder
    ) {
        $this->collection = $collection;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     *
     * Check whether all store groups has assigned root category.
     *
     * @return bool - true if at least one group does not have category
     * @since 2.2.0
     */
    public function isDisplayed()
    {
        return !empty($this->getItems());
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getIdentity()
    {
        return 'empty_assigned_group_category';
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getText()
    {
        $items = $this->getItems();
        $groupLinks = [];
        foreach ($items as $group) {
            $groupUrl = $this->urlBuilder->getUrl('adminhtml/system_store/editGroup', ['group_id' => $group->getId()]);
            $groupLinks[] = sprintf('<a href="%s">%s</a>', $groupUrl, $group->getName());
        }
        return __(
            'The following stores are not associated with a root category: '
            . implode(', ', $groupLinks) . '. For the store to be displayed in the storefront, '
            . 'it must be associated with a root category.'
        );
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }

    /**
     * Retrieves store groups which do not have assigned categories.
     *
     * @return GroupInterface[]
     * @since 2.2.0
     */
    private function getItems()
    {
        if (null === $this->items) {
            $this->items = $this->collection->setWithoutAssignedCategoryFilter()->getItems();
        }
        return $this->items;
    }
}
