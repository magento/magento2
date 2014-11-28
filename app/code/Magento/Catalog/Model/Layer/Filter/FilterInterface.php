<?php
/**
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
