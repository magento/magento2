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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogSearch\Helper;

use Magento\App\Helper\AbstractHelper;
use Magento\App\Helper\Context;
use Magento\CatalogSearch\Model\Fulltext;
use Magento\CatalogSearch\Model\Query;
use Magento\CatalogSearch\Model\QueryFactory;
use Magento\CatalogSearch\Model\Resource\Fulltext\Engine;
use Magento\CatalogSearch\Model\Resource\Query\Collection;
use Magento\Core\Model\Store\ConfigInterface;
use Magento\Escaper;
use Magento\Filter\FilterManager;
use Magento\Stdlib\String;

/**
 * Catalog search helper
 */
class Data extends AbstractHelper
{
    /**
     * Query variable
     */
    const QUERY_VAR_NAME = 'q';

    /**
     * Max query length
     */
    const MAX_QUERY_LEN = 200;

    /**
     * Query object
     *
     * @var Query
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
     * @var Engine
     */
    protected $_engine;

    /**
     * Magento string lib
     *
     * @var String
     */
    protected $string;

    /**
     * Core store config
     *
     * @var ConfigInterface
     */
    protected $_coreStoreConfig;

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
     * Construct
     *
     * @param Context $context
     * @param String $string
     * @param ConfigInterface $coreStoreConfig
     * @param QueryFactory $queryFactory
     * @param Escaper $escaper
     * @param FilterManager $filter
     */
    public function __construct(
        Context $context,
        String $string,
        ConfigInterface $coreStoreConfig,
        QueryFactory $queryFactory,
        Escaper $escaper,
        FilterManager $filter
    ) {
        $this->string = $string;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_queryFactory = $queryFactory;
        $this->_escaper = $escaper;
        $this->filter = $filter;
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
     * @return Query
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
        $thisQueryLength = $this->string->strlen($this->getQueryText());
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
                $this->_queryText = is_array(
                    $this->_queryText
                ) ? '' : $this->string->cleanString(
                    trim($this->_queryText)
                );

                $maxQueryLength = $this->getMaxQueryLength();
                if ($maxQueryLength !== '' && $this->string->strlen($this->_queryText) > $maxQueryLength) {
                    $this->_queryText = $this->string->substr($this->_queryText, 0, $maxQueryLength);
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
        return $this->_escaper->escapeHtml($this->getQueryText());
    }

    /**
     * Retrieve suggest collection for query
     *
     * @return Collection
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
        return $this->_getUrl(
            'catalogsearch/result',
            array('_query' => array(self::QUERY_VAR_NAME => $query), '_secure' => $this->_request->isSecure())
        );
    }

    /**
     * Retrieve suggest url
     *
     * @return string
     */
    public function getSuggestUrl()
    {
        return $this->_getUrl('catalogsearch/ajax/suggest', array('_secure' => $this->_request->isSecure()));
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
        return $this->_coreStoreConfig->getConfig(Query::XML_PATH_MIN_QUERY_LENGTH, $store);
    }

    /**
     * Retrieve maximum query length
     *
     * @param mixed $store
     * @return int|string
     */
    public function getMaxQueryLength($store = null)
    {
        return $this->_coreStoreConfig->getConfig(Query::XML_PATH_MAX_QUERY_LENGTH, $store);
    }

    /**
     * Retrieve maximum query words count for like search
     *
     * @param mixed $store
     * @return int
     */
    public function getMaxQueryWords($store = null)
    {
        return $this->_coreStoreConfig->getConfig(Query::XML_PATH_MAX_QUERY_WORDS, $store);
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
        if ($this->_isMaxLength) {
            $this->addNoteMessage(
                __(
                    'Your search query can\'t be longer than %1, so we had to shorten your query.',
                    $this->getMaxQueryLength()
                )
            );
        }

        $searchType = $this->_coreStoreConfig->getConfig(Fulltext::XML_PATH_CATALOG_SEARCH_TYPE);
        if ($searchType == Fulltext::SEARCH_TYPE_COMBINE || $searchType == Fulltext::SEARCH_TYPE_LIKE) {
            $wordsFull = $this->filter->splitWords($this->getQueryText(), array('uniqueOnly' => true));
            $wordsLike = $this->filter->splitWords(
                $this->getQueryText(),
                array('uniqueOnly' => true, 'wordsQty' => $this->getMaxQueryWords())
            );
            if (count($wordsFull) > count($wordsLike)) {
                $wordsCut = array_map(array($this->_escaper, 'escapeHtml'), array_diff($wordsFull, $wordsLike));
                $this->addNoteMessage(
                    __(
                        'Sorry, but the maximum word count is %1. We left out this part of your search: %2.',
                        $this->getMaxQueryWords(),
                        join(' ', $wordsCut)
                    )
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
            } else {
                $_index = array_merge($_index, $value);
            }
        }
        return join($separator, $_index);
    }
}
