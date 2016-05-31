<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleDefaultHydrator\Api\Data;

interface ExtensionAttributeInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const VALUE = 'value';
    /**#@-*/

    /**
     * Get ID
     *
     * @api
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @api
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get customer ID
     *
     * @api
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set customer ID
     *
     * @api
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get value
     *
     * @api
     * @return string|null
     */
    public function getValue();

    /**
     * Set value
     *
     * @api
     * @param string $value
     * @return $this
     */
    public function setValue($value);
}
