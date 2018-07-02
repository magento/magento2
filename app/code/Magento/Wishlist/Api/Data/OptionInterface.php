<?php

namespace Magento\Wishlist\Api\Data;

interface OptionInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const PRODUCT_ID = 'product_id';

    const VALUE = 'value';

    const CODE = 'code';
    /**#@-*/

    /**
     * Get option id
     */

    public function getId();

    /**
     * Get product id from option
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Get option item
     *
     * @return \Magento\Wishlist\Api\Data\ItemInterface
     */
    public function getItem(): \Magento\Wishlist\Api\Data\ItemInterface;

    /**
     * Set option item
     *
     * @param \Magento\Wishlist\Api\Data\ItemInterface $item
     * @return $this
     */
    public function setItem(\Magento\Wishlist\Api\Data\ItemInterface $item);

    /**
     * Get option product
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    public function getProduct();

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return $this
     */
    public function setProduct(\Magento\Catalog\Api\Data\ProductInterface $product);

    /**
     * Get option value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set option value
     *
     * @param $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Get option code
     *
     * @return mixed
     */
    public function getCode();

    /**
     * Set option code
     *
     * @param $code
     * @return $this
     */
    public function setCode($code);
}
