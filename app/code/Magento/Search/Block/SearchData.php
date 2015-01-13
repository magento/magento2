<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Block;

use Magento\Framework\View\Element\Template;
use Magento\Search\Model\QueryFactoryInterface;
use Magento\Search\Model\QueryInterface;
use Magento\Search\Model\SearchDataProviderInterface;

abstract class SearchData extends Template implements SearchDataInterface
{
    /**
     * @var QueryInterface
     */
    private $query;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var SearchDataProviderInterface
     */
    private $searchDataProvider;

    /**
     * @var string
     */
    protected $_template = 'search_data.phtml';

    /**
     * @param Template\Context $context
     * @param SearchDataProviderInterface $searchDataProvider
     * @param QueryFactoryInterface $queryFactory
     * @param string $title
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        SearchDataProviderInterface $searchDataProvider,
        QueryFactoryInterface $queryFactory,
        $title,
        array $data = []
    ) {
        $this->searchDataProvider = $searchDataProvider;
        $this->query = $queryFactory->get();
        $this->title = $title;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchData()
    {
        return $this->searchDataProvider->getSearchData($this->query);
    }

    /**
     * {@inheritdoc}
     */
    public function isCountResultsEnabled()
    {
        return $this->searchDataProvider->isCountResultsEnabled();
    }

    /**
     * {@inheritdoc}
     */
    public function getLink($queryText)
    {
        return $this->getUrl('*/*/') . '?q=' . urlencode($queryText);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }
}
