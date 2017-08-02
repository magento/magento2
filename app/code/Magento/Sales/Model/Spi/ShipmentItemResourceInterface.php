<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Spi;

/**
 * Interface ResourceInterface
 * @since 2.0.0
 */
interface ShipmentItemResourceInterface
{
    /**
     * Save object data
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @since 2.0.0
     */
    public function save(\Magento\Framework\Model\AbstractModel $object);

    /**
     * Load an object
     *
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string|null $field field to load by (defaults to model id)
     * @return mixed
     * @since 2.0.0
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null);

    /**
     * Delete the object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return mixed
     * @since 2.0.0
     */
    public function delete(\Magento\Framework\Model\AbstractModel $object);
}
