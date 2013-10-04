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
 * Catalog search helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogSearch\Helper;

class Data extends \Magento\Core\Helper\AbstractHelper
{
    const QUERY_VAR_NAME = 'q';
    const MAX_QUERY_LEN  = 200;

    /**
     * Query object
     *
     * @var \Magento\CatalogSearch\Model\Query
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
    protected $_messages = array();

    /**
     * Is a maximum length cut
     *
     * @var bool
     */
    protected $_isMaxLength = false;

    /**
     * Search engine model
     *
     * @var \Magento\CatalogSearch\Model\Resource\Fulltext\Engine
     */
    protected $_engine;

    /**
     * Core string
     *
     * @var \Magento\Core\Helper\String
     */
    protected $_coreString = null;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\ConfigInterface
     */
    protected $_coreStoreConfig;

    /**
     * Query factory
     *
     * @var \Magento\CatalogSearch\Model\QueryFactory
     */
    protected $_queryFactory;

    /**
     * Construct
     * 
     * @param \Magento\Core\Helper\Context $context
     * @param \Magento\Core\Helper\String $coreString
     * @param \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig
     * @param \Magento\CatalogSearch\Model\QueryFactory $queryFactory
     */
    public function __construct(
        \Magento\Core\Helper\Context $context,
        \Magento\Core\Helper\String $coreString,
        \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig,
        \Magento\CatalogSearch\Model\QueryFactory $queryFactory
    ) {
        $this->_coreString = $coreString;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_queryFactory = $queryFactory;
        parent::__construct($context);
    }

    /**
     * Retrieve search query parameter name
     *
     * @return string
     */
    public function getQueryParamName()
    {
        return self::QUERY_VAR_NAME;
    }

    /**
     * Retrieve query model object
     *
     * @return \Magento\CatalogSearch\Model\Query
     */
    public function getQuery()
    {
        if (!$this->_query) {
            $this->_query = $this->_queryFactory->create()->loadByQuery($this->getQueryText());
            if (!$this->_query->getId()) {
                $this->_query->setQueryText($this->getQueryText());
            }
        }
        return $this->_query;
    }

    /**
     * Is a minimum query length
     *
     * @return bool
     */
    public function isMinQueryLength()
    {
        $minQueryLength = $this->getMinQueryLength();
        $thisQueryLength = $this->_coreString->strlen($this->getQueryText());
        return !$thisQueryLength || $minQueryLength !== '' && $thisQueryLength < $minQueryLength;
    }

    /**
     * Retrieve search query text
     *
     * @return string
     */
    public function getQueryText()
    {
        if (!isset($this->_queryText)) {
            $this->_queryText = $this->_getRequest()->getParam($this->getQueryParamName());
            if ($this->_queryText === null) {
                $this->_queryText = '';
            } else {
                /* @var $stringHelper \Magento\Core\Helper\String */
                $stringHelper = $this->_coreString;
                $this->_queryText = is_array($this->_queryText) ? ''
                    : $stringHelper->cleanString(trim($this->_queryText));

                $maxQueryLength = $this->getMaxQueryLength();
                if ($maxQueryLength !== '' && $stringHelper->strlen($this->_queryText) > $maxQueryLength) {
                    $this->_queryText = $stringHelper->substr($this->_queryText, 0, $maxQueryLength);
                    $this->_isMaxLength = true;
                }
            }
        }
        return $this->_queryText;
    }

    /**
     * Retrieve HTML escaped search query
     *
     * @return string
     */
    public function getEscapedQueryText()
    {
        return $this->escapeHtml($this->getQueryText());
    }

    /**
     * Retrieve suggest collection for query
     *
     * @return \Magento\CatalogSearch\Model\Resource\Query\Collection
     */
    public function getSuggestCollection()
    {
        return $this->getQuery()->getSuggestCollection();
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
        return $this->_getUrl('catalogsearch/result', array(
            '_query' => array(self::QUERY_VAR_NAME => $query),
            '_secure' => $this->_request->isSecure()
        ));
    }

    /**
     * Retrieve suggest url
     *
     * @return string
     */
    public function getSuggestUrl()
    {
        return $this->_getUrl('catalogsearch/ajax/suggest', array(
            '_secure' => $this->_request->isSecure()
        ));
    }

    /**
     * Retrieve search term url
     *
     * @return string
     */
    public function getSearchTermUrl()
    {
        return $this->_getUrl('catalogsearch/term/popular');
    }

    /**
     * Retrieve advanced search URL
     *
     * @return string
     */
    public function getAdvancedSearchUrl()
    {
        return $this->_getUrl('catalogsearch/advanced');
    }

    /**
     * Retrieve minimum query length
     *
     * @param mixed $store
     * @return int|string
     */
    public function getMinQueryLength($store = null)
    {
        return $this->_coreStoreConfig->getConfig(\Magento\CatalogSearch\Model\Query::XML_PATH_MIN_QUERY_LENGTH, $store);
    }

    /**
     * Retrieve maximum query length
     *
     * @param mixed $store
     * @return int|string
     */
    public function getMaxQueryLength($store = null)
    {
        return $this->_coreStoreConfig->getConfig(\Magento\CatalogSearch\Model\Query::XML_PATH_MAX_QUERY_LENGTH, $store);
    }

    /**
     * Retrieve maximum query words count for like search
     *
     * @param mixed $store
     * @return int
     */
    public function getMaxQueryWords($store = null)
    {
        return $this->_coreStoreConfig->getConfig(\Magento\CatalogSearch\Model\Query::XML_PATH_MAX_QUERY_WORDS, $store);
    }

    /**
     * Add Note message
     *
     * @param string $message
     * @return \Magento\CatalogSearch\Helper\Data
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
     * @return \Magento\CatalogSearch\Helper\Data
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
     * @return \Magento\CatalogSearch\Helper\Data
     */
    public function checkNotes($store = null)
    {
        if ($this->_isMaxLength) {
            $this->addNoteMessage(__('Your search query can\'t be longer than %1, so we had to shorten your query.', $this->getMaxQueryLength()));
        }

        /* @var $stringHelper \Magento\Core\Helper\String */
        $stringHelper = $this->_coreString;

        $searchType = $this->_coreStoreConfig->getConfig(\Magento\CatalogSearch\Model\Fulltext::XML_PATH_CATALOG_SEARCH_TYPE);
        if ($searchType == \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_COMBINE
            || $searchType == \Magento\CatalogSearch\Model\Fulltext::SEARCH_TYPE_LIKE
        ) {
            $wordsFull = $stringHelper->splitWords($this->getQueryText(), true);
            $wordsLike = $stringHelper->splitWords($this->getQueryText(), true, $this->getMaxQueryWords());
            if (count($wordsFull) > count($wordsLike)) {
                $wordsCut = array_map(array($this, 'escapeHtml'), array_diff($wordsFull, $wordsLike));
                $this->addNoteMessage(
                    __('Sorry, but the maximum word count is %1. We left out this part of your search: %2.', $this->getMaxQueryWords(), join(' ', $wordsCut))
                );
            }
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
        $_index = array();
        foreach ($index as $value) {
            if (!is_array($value)) {
                $_index[] = $value;
            }
            else {
                $_index = array_merge($_index, $value);
            }
        }
        return join($separator, $_index);
    }
}
