<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Test\Unit\Helper;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Test helper for Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
 *
 * Extends AbstractCollection to add custom methods for testing
 */
class AbstractCollectionTestHelper extends AbstractCollection
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // Skip parent constructor to avoid dependencies
    }

    /**
     * Set product ID filter for testing
     *
     * @param mixed $productId
     * @return self
     */
    public function setProductIdFilter($productId): self
    {
        $this->data['product_id'] = $productId;
        return $this;
    }

    /**
     * Set position order for testing
     *
     * @return self
     */
    public function setPositionOrder(): self
    {
        $this->data['position_order'] = true;
        return $this;
    }

    /**
     * Join values for testing
     *
     * @param mixed $storeId
     * @return self
     */
    public function joinValues($storeId = null): self
    {
        $this->data['join_values'] = $storeId;
        return $this;
    }

    /**
     * Set ID filter for testing
     *
     * @param mixed $ids
     * @return self
     */
    public function setIdFilter($ids): self
    {
        $this->data['id_filter'] = $ids;
        return $this;
    }
}
