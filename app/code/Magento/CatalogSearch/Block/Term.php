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

/**
 * Catalogsearch term block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogSearch\Block;

use Magento\CatalogSearch\Model\Resource\Query\CollectionFactory;
use Magento\Framework\UrlFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Term extends Template
{
    /**
     * @var array
     */
    protected $_terms;

    /**
     * @var int
     */
    protected $_minPopularity;

    /**
     * @var int
     */
    protected $_maxPopularity;

    /**
     * Url factory
     *
     * @var UrlFactory
     */
    protected $_urlFactory;

    /**
     * Query collection factory
     *
     * @var CollectionFactory
     */
    protected $_queryCollectionFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $queryCollectionFactory
     * @param UrlFactory $urlFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $queryCollectionFactory,
        UrlFactory $urlFactory,
        array $data = array()
    ) {
        $this->_queryCollectionFactory = $queryCollectionFactory;
        $this->_urlFactory = $urlFactory;
        parent::__construct($context, $data);
    }

    /**
     * Load terms and try to sort it by names
     *
     * @return $this
     */
    protected function _loadTerms()
    {
        if (empty($this->_terms)) {
            $this->_terms = array();
            $terms = $this->_queryCollectionFactory->create()->setPopularQueryFilter(
                $this->_storeManager->getStore()->getId()
            )->setPageSize(
                100
            )->load()->getItems();

            if (count($terms) == 0) {
                return $this;
            }


            $this->_maxPopularity = reset($terms)->getPopularity();
            $this->_minPopularity = end($terms)->getPopularity();
            $range = $this->_maxPopularity - $this->_minPopularity;
            $range = $range == 0 ? 1 : $range;
            foreach ($terms as $term) {
                if (!$term->getPopularity()) {
                    continue;
                }
                $term->setRatio(($term->getPopularity() - $this->_minPopularity) / $range);
                $temp[$term->getName()] = $term;
                $termKeys[] = $term->getName();
            }
            natcasesort($termKeys);

            foreach ($termKeys as $termKey) {
                $this->_terms[$termKey] = $temp[$termKey];
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getTerms()
    {
        $this->_loadTerms();
        return $this->_terms;
    }

    /**
     * @param \Magento\Framework\Object $obj
     * @return string
     */
    public function getSearchUrl($obj)
    {
        /** @var $url UrlInterface */
        $url = $this->_urlFactory->create();
        /*
         * url encoding will be done in Url.php http_build_query
         * so no need to explicitly called urlencode for the text
         */
        $url->setQueryParam('q', $obj->getName());
        return $url->getUrl('catalogsearch/result');
    }

    /**
     * @return int
     */
    public function getMaxPopularity()
    {
        return $this->_maxPopularity;
    }

    /**
     * @return int
     */
    public function getMinPopularity()
    {
        return $this->_minPopularity;
    }
}
