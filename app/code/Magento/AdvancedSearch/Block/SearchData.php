<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Search\Model\QueryFactoryInterface;
use Magento\Search\Model\QueryInterface;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;

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
     * @var SuggestedQueriesInterface
     */
    private $searchDataProvider;

    /**
     * @var string
     */
    protected $_template = 'Magento_AdvancedSearch::search_data.phtml';

    /**
     * @param Template\Context $context
     * @param SuggestedQueriesInterface $searchDataProvider
     * @param QueryFactoryInterface $queryFactory
     * @param string $title
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        SuggestedQueriesInterface $searchDataProvider,
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
    public function getItems()
    {
        return $this->searchDataProvider->getItems($this->query);
    }

    /**
     * {@inheritdoc}
     */
    public function isShowResultsCount()
    {
        return $this->searchDataProvider->isResultsCountEnabled();
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
        return __($this->title);
    }
}
