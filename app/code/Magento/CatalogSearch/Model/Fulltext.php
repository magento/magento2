<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\Db;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\Resource\AbstractResource;
use Magento\Framework\Registry;
use Magento\Search\Model\QueryFactory;

/**
 * Catalog advanced search model
 *
 * @method \Magento\CatalogSearch\Model\Resource\Fulltext _getResource()
 * @method \Magento\CatalogSearch\Model\Resource\Fulltext getResource()
 * @method int getProductId()
 * @method \Magento\CatalogSearch\Model\Fulltext setProductId(int $value)
 * @method int getStoreId()
 * @method \Magento\CatalogSearch\Model\Fulltext setStoreId(int $value)
 * @method string getDataIndex()
 * @method \Magento\CatalogSearch\Model\Fulltext setDataIndex(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Fulltext extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Catalog search data
     *
     * @var QueryFactory
     */
    protected $queryFactory = null;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param QueryFactory $queryFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param AbstractResource $resource
     * @param Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        QueryFactory $queryFactory,
        ScopeConfigInterface $scopeConfig,
        AbstractResource $resource = null,
        Db $resourceCollection = null,
        array $data = []
    ) {
        $this->queryFactory = $queryFactory;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\CatalogSearch\Model\Resource\Fulltext');
    }

    /**
     * Reset search results cache
     *
     * @return $this
     */
    public function resetSearchResults()
    {
        $this->getResource()->resetSearchResults();
        return $this;
    }
}
