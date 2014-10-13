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
namespace Magento\CatalogSearch\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\Db;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\Resource\AbstractResource;
use Magento\Framework\Registry;
use Magento\Search\Model\Query;
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
    const SEARCH_TYPE_LIKE = 1;

    const SEARCH_TYPE_FULLTEXT = 2;

    const SEARCH_TYPE_COMBINE = 3;

    const XML_PATH_CATALOG_SEARCH_TYPE = 'catalog/search/search_type';

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
        array $data = array()
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

    /**
     * Prepare results for query
     *
     * @param Query $query
     * @return $this
     */
    public function prepareResult($query = null)
    {
        if (!$query instanceof Query) {
            $query = $this->queryFactory->get();
        }
        $queryText = $query->getQueryText();
        if ($query->getSynonymFor()) {
            $queryText = $query->getSynonymFor();
        }
        $this->getResource()->prepareResult($this, $queryText, $query);
        return $this;
    }

    /**
     * Retrieve search type
     *
     * @param int $storeId
     * @return int
     */
    public function getSearchType($storeId = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_CATALOG_SEARCH_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
