<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer\Filter;

/**
 * Layer filter interface
 */
interface FilterInterface
{
    /**
     * Set request variable name which is used for apply filter
     *
     * @param   string $varName
     * @return  \Magento\Catalog\Model\Layer\Filter\FilterInterface
     */
    public function setRequestVar($varName);

    /**
     * Get request variable name which is used for apply filter
     *
     * @return string
     */
    public function getRequestVar();

    /**
     * Get filter value for reset current filter state
     *
     * @return mixed
     */
    public function getResetValue();

    /**
     * Retrieve filter value for Clear All Items filter state
     *
     * @return mixed
     */
    public function getCleanValue();

    /**
     * Apply filter to collection
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request);

    /**
     * Get filter items count
     *
     * @return int
     */
    public function getItemsCount();

    /**
     * Get all filter items
     *
     * @return array
     */
    public function getItems();

    /**
     * Set all filter items
     *
     * @param array $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Retrieve layer object
     *
     * @return \Magento\Catalog\Model\Layer
     */
    public function getLayer();

    /**
     * Set attribute model to filter
     *
     * @param   \Magento\Eav\Model\Entity\Attribute $attribute
     * @return  \Magento\Catalog\Model\Layer\Filter\FilterInterface
     */
    public function setAttributeModel($attribute);

    /**
     * Get attribute model associated with filter
     *
     * @return \Magento\Catalog\Model\Resource\Eav\Attribute
     * @throws \Magento\Framework\Model\Exception
     */
    public function getAttributeModel();

    /**
     * Get filter text label
     *
     * @return string
     * @throws \Magento\Framework\Model\Exception
     */
    public function getName();

    /**
     * Retrieve current store id scope
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store id scope
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Retrieve Website ID scope
     *
     * @return int
     */
    public function getWebsiteId();

    /**
     * Set Website ID scope
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * Clear current element link text, for example 'Clear Price'
     *
     * @return false|string
     */
    public function getClearLinkText();
}
