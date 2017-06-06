<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Search term block
 */
namespace Magento\Search\Block;

use Magento\Framework\UrlFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory;

/**
 * @api
 */
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
        array $data = []
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
            $this->_terms = [];
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
                $temp[$term->getQueryText()] = $term;
                $termKeys[] = $term->getQueryText();
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
     * @param \Magento\Framework\DataObject $obj
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
        $url->setQueryParam('q', $obj->getQueryText());
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
