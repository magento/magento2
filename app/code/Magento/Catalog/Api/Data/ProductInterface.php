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

namespace Magento\Catalog\Api\Data;

interface ProductInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const SKU = 'sku';

    const NAME = 'name';

    const PRICE = 'price';

    const WEIGHT = 'weight';

    const STATUS = 'status';

    const VISIBILITY = 'visibility';

    const ATTRIBUTE_SET_ID = 'attribute_set_id';

    const TYPE_ID = 'type_id';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    const STORE_ID = 'store_id';
    /**#@-*/

    /**
     * Product id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Product sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Product name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Product store id
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Product attribute set id
     *
     * @return int|null
     */
    public function getAttributeSetId();

    /**
     * Product price
     *
     * @return float|null
     */
    public function getPrice();

    /**
     * Product status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Product visibility
     *
     * @return int|null
     */
    public function getVisibility();

    /**
     * Product type id
     *
     * @return string|null
     */
    public function getTypeId();

    /**
     * Product created date
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Product updated date
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Product weight
     *
     * @return float|null
     */
    public function getWeight();
}
