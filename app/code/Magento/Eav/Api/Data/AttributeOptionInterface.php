<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
