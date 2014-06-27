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
namespace Magento\Backend\Model\Search;

/**
 * Search Catalog Model
 */
class Catalog extends \Magento\Framework\Object
{
    /**
     * Catalog search data
     *
     * @var \Magento\CatalogSearch\Helper\Data
     */
    protected $_catalogSearchData = null;

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminhtmlData = null;

    /**
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Framework\Stdlib\String $string
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchData
     */
    public function __construct(
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Framework\Stdlib\String $string,
        \Magento\CatalogSearch\Helper\Data $catalogSearchData
    ) {
        $this->_adminhtmlData = $adminhtmlData;
        $this->string = $string;
        $this->_catalogSearchData = $catalogSearchData;
    }

    /**
     * Load search results
     *
     * @return $this
     */
    public function load()
    {
        $result = array();
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($result);
            return $this;
        }

        $collection = $this->_catalogSearchData->getQuery()->getSearchCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'description'
        )->addBackendSearchFilter(
            $this->getQuery()
        )->setCurPage(
            $this->getStart()
        )->setPageSize(
            $this->getLimit()
        )->load();

        foreach ($collection as $product) {
            $description = strip_tags($product->getDescription());
            $result[] = array(
                'id' => 'product/1/' . $product->getId(),
                'type' => __('Product'),
                'name' => $product->getName(),
                'description' => $this->string->substr($description, 0, 30),
                'url' => $this->_adminhtmlData->getUrl('catalog/product/edit', array('id' => $product->getId()))
            );
        }

        $this->setResults($result);

        return $this;
    }
}
