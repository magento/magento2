<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Stdlib\String;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\Query as SearchQuery;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\Resource\Query\Collection;

/**
 * Search helper
 */
class Data extends AbstractHelper
{
    /**
     * @var array
     */
    protected $_suggestData = null;

    /**
     * Query object
     *
     * @var SearchQuery
     */
    protected $_query;

    /**
     * Query string
     *
     * @var string
     */
    protected $_queryText;

    /**
     * Note messages
     *
     * @var array
     */
    protected $_messages = [];

    /**
     * Magento string lib
     *
     * @var String
     */
    protected $string;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Query factory
     *
     * @var QueryFactory
     */
    protected $_queryFactory;

    /**
     * @var Escaper
     */
    protected $_escaper;

    /**
     * @var FilterManager
     */
    protected $filter;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param Context $context
     * @param String $string
     * @param ScopeConfigInterface $scopeConfig
     * @param QueryFactory $queryFactory
     * @param Escaper $escaper
     * @param FilterManager $filter
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        String $string,
        ScopeConfigInterface $scopeConfig,
        QueryFactory $queryFactory,
        Escaper $escaper,
        FilterManager $filter,
        StoreManagerInterface $storeManager
    ) {
        $this->string = $string;
        $this->_scopeConfig = $scopeConfig;
        $this->_queryFactory = $queryFactory;
        $this->_escaper = $escaper;
        $this->filter = $filter;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Is a minimum query length
     *
     * @return bool
     */
    public function isMinQueryLength()
    {
        $minQueryLength = $this->getMinQueryLength();
        $thisQueryLength = $this->string->strlen($this->_queryFactory->get()->getQueryText());
        return !$thisQueryLength || $minQueryLength !== '' && $thisQueryLength < $minQueryLength;
    }

    /**
     * Retrieve HTML escaped search query
     *
     * @return string
     */
    public function getEscapedQueryText()
    {
        return $this->_escaper->escapeHtml($this->_queryFactory->get()->getQueryText());
    }

    /**
     * Retrieve suggest collection for query
     *
     * @return Collection
     */
    public function getSuggestCollection()
    {
        return $this->_queryFactory->get()->getSuggestCollection();
    }

    /**
     * Retrieve result page url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @param   string $query
     * @return  string
     */
    public function getResultUrl($query = null)
    {
        return $this->_getUrl(
            'catalogsearch/result',
            ['_query' => [QueryFactory::QUERY_VAR_NAME => $query], '_secure' => $this->_request->isSecure()]
        );
    }

    /**
     * Retrieve suggest url
     *
     * @return string
     */
    public function getSuggestUrl()
    {
        return $this->_getUrl(
            'search/ajax/suggest',
            ['_secure' => $this->_storeManager->getStore()->isCurrentlySecure()]
        );
    }

    /**
     * Retrieve search term url
     *
     * @return string
     */
    public function getSearchTermUrl()
    {
        return $this->_getUrl('search/term/popular');
    }

    /**
     * Retrieve minimum query length
     *
     * @param mixed $store
     * @return int|string
     */
    public function getMinQueryLength($store = null)
    {
        return $this->_scopeConfig->getValue(
            SearchQuery::XML_PATH_MIN_QUERY_LENGTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve maximum query length
     *
     * @param mixed $store
     * @return int|string
     */
    public function getMaxQueryLength($store = null)
    {
        return $this->_scopeConfig->getValue(
            SearchQuery::XML_PATH_MAX_QUERY_LENGTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Add Note message
     *
     * @param string $message
     * @return $this
     */
    public function addNoteMessage($message)
    {
        $this->_messages[] = $message;
        return $this;
    }

    /**
     * Set Note messages
     *
     * @param array $messages
     * @return $this
     */
    public function setNoteMessages(array $messages)
    {
        $this->_messages = $messages;
        return $this;
    }

    /**
     * Retrieve Current Note messages
     *
     * @return array
     */
    public function getNoteMessages()
    {
        return $this->_messages;
    }

    /**
     * Check query of a warnings
     *
     * @param mixed $store
     * @return $this
     */
    public function checkNotes($store = null)
    {
        if ($this->_queryFactory->get()->isQueryTextExceeded()) {
            $this->addNoteMessage(
                __(
                    'Your search query can\'t be longer than %1, so we had to shorten your query.',
                    $this->getMaxQueryLength()
                )
            );
        }

        return $this;
    }

    /**
     * Join index array to string by separator
     * Support 2 level array gluing
     *
     * @param array $index
     * @param string $separator
     * @return string
     */
    public function prepareIndexdata($index, $separator = ' ')
    {
        $_index = [];
        foreach ($index as $value) {
            if (!is_array($value)) {
                $_index[] = $value;
            } else {
                $_index = array_merge($_index, $value);
            }
        }
        return join($separator, array_filter($_index));
    }

    /**
     * @return array
     */
    public function getSuggestData()
    {
        if (!$this->_suggestData) {
            $collection = $this->getSuggestCollection();
            $query = $this->_queryFactory->get()->getQueryText();
            $counter = 0;
            $data = [];
            foreach ($collection as $item) {
                $_data = [
                    'title' => $item->getQueryText(),
                    'row_class' => ++$counter % 2 ? 'odd' : 'even',
                    'num_of_results' => $item->getNumResults(),
                ];

                if ($item->getQueryText() == $query) {
                    array_unshift($data, $_data);
                } else {
                    $data[] = $_data;
                }
            }
            $this->_suggestData = $data;
        }
        return $this->_suggestData;
    }

    /**
     * @return string
     */
    public function getQueryParamName()
    {
        return QueryFactory::QUERY_VAR_NAME;
    }
}
