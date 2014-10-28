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
namespace Magento\Search\Block;

use Magento\Framework\View\Element\Template;
use Magento\Search\Model\SearchDataProviderInterface;
use Magento\Search\Model\QueryInterface;
use Magento\Search\Model\QueryFactoryInterface;

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
        array $data = array()
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
