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

/**
 * Class \Magento\AdvancedSearch\Block\SearchData
 *
 * @since 2.0.0
 */
abstract class SearchData extends Template implements SearchDataInterface
{
    /**
     * @var QueryInterface
     * @since 2.0.0
     */
    private $query;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $title;

    /**
     * @var SuggestedQueriesInterface
     * @since 2.0.0
     */
    private $searchDataProvider;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'search_data.phtml';

    /**
     * @param Template\Context $context
     * @param SuggestedQueriesInterface $searchDataProvider
     * @param QueryFactoryInterface $queryFactory
     * @param string $title
     * @param array $data
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getItems()
    {
        return $this->searchDataProvider->getItems($this->query);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isShowResultsCount()
    {
        return $this->searchDataProvider->isResultsCountEnabled();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getLink($queryText)
    {
        return $this->getUrl('*/*/') . '?q=' . urlencode($queryText);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTitle()
    {
        return __($this->title);
    }
}
