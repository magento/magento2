<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Eav\Api\Data;

/**
 * Interface AttributeOptionLabelInterface
 * @api
 * @since 100.0.2
 */
interface AttributeOptionLabelInterface
{
    const LABEL = 'label';

    const STORE_ID = 'store_id';

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get option label
     *
     * @return string|null
     */
    public function getLabel();

    /**
     * Set option label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label);
}
