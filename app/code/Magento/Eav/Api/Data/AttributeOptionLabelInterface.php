<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

/**
 * Interface AttributeOptionLabelInterface
 * @api
 * @since 2.0.0
 */
interface AttributeOptionLabelInterface
{
    const LABEL = 'label';

    const STORE_ID = 'store_id';

    /**
     * Get store id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     * @since 2.0.0
     */
    public function setStoreId($storeId);

    /**
     * Get option label
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getLabel();

    /**
     * Set option label
     *
     * @param string $label
     * @return $this
     * @since 2.0.0
     */
    public function setLabel($label);
}
