<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

/**
 * Created from:
 * @codeCoverageIgnore
 * @api
 * @since 2.0.0
 */
interface AttributeOptionInterface
{
    /**
     * Constants used as data array keys
     */
    const LABEL = 'label';

    const VALUE = 'value';

    const SORT_ORDER = 'sort_order';

    const STORE_LABELS = 'store_labels';

    const IS_DEFAULT = 'is_default';

    /**
     * Get option label
     *
     * @return string
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

    /**
     * Get option value
     *
     * @return string
     * @since 2.0.0
     */
    public function getValue();

    /**
     * Set option value
     *
     * @param string $value
     * @return string
     * @since 2.0.0
     */
    public function setValue($value);

    /**
     * Get option order
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getSortOrder();

    /**
     * Set option order
     *
     * @param int $sortOrder
     * @return $this
     * @since 2.0.0
     */
    public function setSortOrder($sortOrder);

    /**
     * is default
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function getIsDefault();

    /**
     * set is default
     *
     * @param bool $isDefault
     * @return $this
     * @since 2.0.0
     */
    public function setIsDefault($isDefault);

    /**
     * Get option label for store scopes
     *
     * @return \Magento\Eav\Api\Data\AttributeOptionLabelInterface[]|null
     * @since 2.0.0
     */
    public function getStoreLabels();

    /**
     * Set option label for store scopes
     *
     * @param \Magento\Eav\Api\Data\AttributeOptionLabelInterface[] $storeLabels
     * @return $this
     * @since 2.0.0
     */
    public function setStoreLabels(array $storeLabels = null);
}
