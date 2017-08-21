<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\Query as SearchQuery;
use Magento\Search\Model\QueryFactory;

/**
 * Search helper
 * @api
 */
class Data extends AbstractHelper
{
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
     * @since 100.1.0
     */
    protected $scopeConfig;

    /**
     * @var Escaper
     * @since 100.1.0
     */
    protected $escaper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 100.1.0
     */
    protected $storeManager;

    /**
     * Construct
     *
     * @param Context $context
     * @param StringUtils $string
     * @param Escaper $escaper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StringUtils $string,
        Escaper $escaper,
        StoreManagerInterface $storeManager
    ) {
        $this->string = $string;
        $this->scopeConfig = $context->getScopeConfig();
        $this->escaper = $escaper;
        $this->storeManager = $storeManager;
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
        $thisQueryLength = $this->string->strlen($this->getQueryText());
        return !$thisQueryLength || $minQueryLength !== '' && $thisQueryLength < $minQueryLength;
    }

    /**
     * Retrieve HTML escaped search query
     *
     * @return string
     */
    public function getEscapedQueryText()
    {
        return $this->escaper->escapeHtml(
            $this->getPreparedQueryText($this->getQueryText(), $this->getMaxQueryLength())
        );
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
            ['_secure' => $this->storeManager->getStore()->isCurrentlySecure()]
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
        return $this->scopeConfig->getValue(
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
        return $this->scopeConfig->getValue(
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkNotes($store = null)
    {
        if ($this->isQueryTooLong($this->getQueryText(), $this->getMaxQueryLength())) {
            $this->addNoteMessage(
                __(
                    'Your search query can\'t be longer than %1, so we shortened your query.',
                    $this->getMaxQueryLength()
                )
            );
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getQueryParamName()
    {
        return QueryFactory::QUERY_VAR_NAME;
    }

    /**
     * @param string $queryText
     * @param int|string $maxQueryLength
     * @return bool
     */
    private function isQueryTooLong($queryText, $maxQueryLength)
    {
        return ($maxQueryLength !== '' && $this->string->strlen($queryText) > $maxQueryLength);
    }

    /**
     * Retrieve search query text
     *
     * @return string
     */
    private function getQueryText()
    {
        $queryText = $this->_request->getParam($this->getQueryParamName());
        return($queryText === null || is_array($queryText))
            ? ''
            : $this->string->cleanString(trim($queryText));
    }

    /**
     * @param string $queryText
     * @param int|string $maxQueryLength
     * @return string
     */
    private function getPreparedQueryText($queryText, $maxQueryLength)
    {
        if ($this->isQueryTooLong($queryText, $maxQueryLength)) {
            $queryText = $this->string->substr($queryText, 0, $maxQueryLength);
        }
        return $queryText;
    }
}
