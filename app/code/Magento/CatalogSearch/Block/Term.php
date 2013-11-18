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
 * @category    Magento
 * @package     Magento_CatalogSearch
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalogsearch term block
 *
 * @category   Magento
 * @package    Magento_CatalogSearch
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogSearch\Block;

class Term extends \Magento\Core\Block\Template
{
    protected $_terms;
    protected $_minPopularity;
    protected $_maxPopularity;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Url factory
     *
     * @var \Magento\Core\Model\UrlFactory
     */
    protected $_urlFactory;

    /**
     * Query collection factory
     *
     * @var \Magento\CatalogSearch\Model\Resource\Query\CollectionFactory
     */
    protected $_queryCollectionFactory;

    /**
     * Construct
     *
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\CatalogSearch\Model\Resource\Query\CollectionFactory $queryCollectionFactory
     * @param \Magento\Core\Model\UrlFactory $urlFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\CatalogSearch\Model\Resource\Query\CollectionFactory $queryCollectionFactory,
        \Magento\Core\Model\UrlFactory $urlFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        array $data = array()
    ) {
        $this->_queryCollectionFactory = $queryCollectionFactory;
        $this->_urlFactory = $urlFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Load terms and try to sort it by names
     *
     * @return \Magento\CatalogSearch\Block\Term
     */
    protected function _loadTerms()
    {
        if (empty($this->_terms)) {
            $this->_terms = array();
            $terms = $this->_queryCollectionFactory->create()
                ->setPopularQueryFilter($this->_storeManager->getStore()->getId())
                ->setPageSize(100)
                ->load()
                ->getItems();

            if( count($terms) == 0 ) {
                return $this;
            }


            $this->_maxPopularity = reset($terms)->getPopularity();
            $this->_minPopularity = end($terms)->getPopularity();
            $range = $this->_maxPopularity - $this->_minPopularity;
            $range = ( $range == 0 ) ? 1 : $range;
            foreach ($terms as $term) {
                if( !$term->getPopularity() ) {
                    continue;
                }
                $term->setRatio(($term->getPopularity()-$this->_minPopularity)/$range);
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

    public function getTerms()
    {
        $this->_loadTerms();
        return $this->_terms;
    }

    public function getSearchUrl($obj)
    {
        /** @var $url \Magento\Core\Model\Url */
        $url = $this->_urlFactory->create();
        /*
        * url encoding will be done in Url.php http_build_query
        * so no need to explicitly called urlencode for the text
        */
        $url->setQueryParam('q', $obj->getName());
        return $url->getUrl('catalogsearch/result');
    }

    public function getMaxPopularity()
    {
        return $this->_maxPopularity;
    }

    public function getMinPopularity()
    {
        return $this->_minPopularity;
    }
}
