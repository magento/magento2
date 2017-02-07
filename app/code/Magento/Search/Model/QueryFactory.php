<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

use Magento\Search\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\StringUtils as StdlibString;

class QueryFactory implements QueryFactoryInterface
{
    /**
     * Query variable
     */
    const QUERY_VAR_NAME = 'q';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StdlibString
     */
    private $string;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Data
     */
    private $queryHelper;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StdlibString $string
     * @param Data|null $queryHelper
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StdlibString $string,
        Data $queryHelper = null
    ) {
        $this->request = $context->getRequest();
        $this->objectManager = $objectManager;
        $this->string = $string;
        $this->scopeConfig = $context->getScopeConfig();
        $this->queryHelper = $queryHelper === null ? $this->objectManager->get(Data::class) : $queryHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (!$this->query) {
            $maxQueryLength = $this->queryHelper->getMaxQueryLength();
            $minQueryLength = $this->queryHelper->getMinQueryLength();
            $rawQueryText = $this->getRawQueryText();
            $preparedQueryText = $this->getPreparedQueryText($rawQueryText, $maxQueryLength);
            $query = $this->create()->loadByQueryText($preparedQueryText);
            if (!$query->getId()) {
                $query->setQueryText($preparedQueryText);
            }
            $query->setIsQueryTextExceeded($this->isQueryTooLong($rawQueryText, $maxQueryLength));
            $query->setIsQueryTextShort($this->isQueryTooShort($rawQueryText, $minQueryLength));
            $this->query = $query;
        }
        return $this->query;
    }

    /**
     * Create new instance
     *
     * @param array $data
     * @return Query
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(Query::class, $data);
    }

    /**
     * Retrieve search query text
     *
     * @return string
     */
    private function getRawQueryText()
    {
        $queryText = $this->request->getParam(self::QUERY_VAR_NAME);
        return ($queryText === null || is_array($queryText))
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
     * @param string $queryText
     * @param int|string $minQueryLength
     * @return bool
     */
    private function isQueryTooShort($queryText, $minQueryLength)
    {
        return ($this->string->strlen($queryText) < $minQueryLength);
    }
}
