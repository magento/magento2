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
namespace Magento\Sales\Service\V1\Action;

use Magento\Sales\Service\V1\Data\CommentMapper;
use Magento\Framework\Service\V1\Data\FilterBuilder;
use Magento\Sales\Model\Order\Invoice\CommentRepository;
use Magento\Framework\Service\V1\Data\SearchCriteriaBuilder;
use Magento\Sales\Service\V1\Data\CommentSearchResultsBuilder;

/**
 * Class InvoiceCommentsList
 */
class InvoiceCommentsList
{
    /**
     * @var \Magento\Sales\Model\Order\Invoice\CommentRepository
     */
    protected $commentRepository;

    /**
     * @var \Magento\Sales\Service\V1\Data\CommentMapper
     */
    protected $commentMapper;

    /**
     * @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * @var \Magento\Framework\Service\V1\Data\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Sales\Service\V1\Data\CommentSearchResultsBuilder
     */
    protected $searchResultsBuilder;

    /**
     * @param \Magento\Sales\Model\Order\Invoice\CommentRepository $commentRepository
     * @param \Magento\Sales\Service\V1\Data\CommentMapper $commentMapper
     * @param \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Service\V1\Data\FilterBuilder $filterBuilder
     * @param \Magento\Sales\Service\V1\Data\CommentSearchResultsBuilder $searchResultsBuilder
     */
    public function __construct(
        CommentRepository $commentRepository,
        CommentMapper $commentMapper,
        SearchCriteriaBuilder $criteriaBuilder,
        FilterBuilder $filterBuilder,
        CommentSearchResultsBuilder $searchResultsBuilder
    ) {
        $this->commentRepository = $commentRepository;
        $this->commentMapper = $commentMapper;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->searchResultsBuilder = $searchResultsBuilder;
    }

    /**
     * Invoke InvoiceCommentsList service
     *
     * @param int $id
     * @return \Magento\Sales\Service\V1\Data\CommentSearchResults
     */
    public function invoke($id)
    {
        $this->criteriaBuilder->addFilter(
            ['eq' => $this->filterBuilder->setField('parent_id')->setValue($id)->create()]
        );
        $criteria = $this->criteriaBuilder->create();
        $comments = [];
        foreach ($this->commentRepository->find($criteria) as $comment) {
            $comments[] = $this->commentMapper->extractDto($comment);
        }
        return $this->searchResultsBuilder->setItems($comments)
            ->setSearchCriteria($criteria)
            ->setTotalCount(count($comments))
            ->create();
    }
}
