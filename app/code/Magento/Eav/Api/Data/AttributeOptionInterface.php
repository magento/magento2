<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

/**
 * Created from:
 * @codeCoverageIgnore
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
     */
    public function getLabel();

    /**
     * Get option value
     *
     * @return string
     */
    public function getValue();

    /**
     * Get option order
     *
     * @return int|null
     */
    public function getSortOrder();

    /**
     * is default
     *
     * @return bool|nulll
     */
    public function getIsDefault();

    /**
     * Set option label for store scopes
     *
     * @return \Magento\Eav\Api\Data\AttributeOptionLabelInterface[]|null
     */
    public function getStoreLabels();
}
