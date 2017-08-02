<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer\Filter;

/**
 * Layer filter interface
 *
 * @api
 * @since 2.0.0
 */
interface FilterInterface
{
    /**
     * Set request variable name which is used for apply filter
     *
     * @param   string $varName
     * @return  \Magento\Catalog\Model\Layer\Filter\FilterInterface
     * @since 2.0.0
     */
    public function setRequestVar($varName);

    /**
     * Get request variable name which is used for apply filter
     *
     * @return string
     * @since 2.0.0
     */
    public function getRequestVar();

    /**
     * Get filter value for reset current filter state
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getResetValue();

    /**
     * Retrieve filter value for Clear All Items filter state
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getCleanValue();

    /**
     * Apply filter to collection
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @since 2.0.0
     */
    public function apply(\Magento\Framework\App\RequestInterface $request);

    /**
     * Get filter items count
     *
     * @return int
     * @since 2.0.0
     */
    public function getItemsCount();

    /**
     * Get all filter items
     *
     * @return array
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set all filter items
     *
     * @param array $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items);

    /**
     * Retrieve layer object
     *
     * @return \Magento\Catalog\Model\Layer
     * @since 2.0.0
     */
    public function getLayer();

    /**
     * Set attribute model to filter
     *
     * @param   \Magento\Eav\Model\Entity\Attribute $attribute
     * @return  \Magento\Catalog\Model\Layer\Filter\FilterInterface
     * @since 2.0.0
     */
    public function setAttributeModel($attribute);

    /**
     * Get attribute model associated with filter
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getAttributeModel();

    /**
     * Get filter text label
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getName();

    /**
     * Retrieve current store id scope
     *
     * @return int
     * @since 2.0.0
     */
    public function getStoreId();

    /**
     * Set store id scope
     *
     * @param int $storeId
     * @return $this
     * @since 2.0.0
     */
    public function setStoreId($storeId);

    /**
     * Retrieve Website ID scope
     *
     * @return int
     * @since 2.0.0
     */
    public function getWebsiteId();

    /**
     * Set Website ID scope
     *
     * @param int $websiteId
     * @return $this
     * @since 2.0.0
     */
    public function setWebsiteId($websiteId);

    /**
     * Clear current element link text, for example 'Clear Price'
     *
     * @return false|string
     * @since 2.0.0
     */
    public function getClearLinkText();
}
