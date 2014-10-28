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
namespace Magento\Sales\Service\V1;

use Magento\Sales\Service\V1\Action\CreditmemoGet;
use Magento\Sales\Service\V1\Action\CreditmemoList;
use Magento\Sales\Service\V1\Action\CreditmemoCommentsList;
use Magento\Framework\Service\V1\Data\SearchCriteria;

/**
 * Class CreditmemoRead
 */
class CreditmemoRead implements CreditmemoReadInterface
{
    /**
     * @var CreditmemoGet
     */
    protected $creditmemoGet;

    /**
     * @var CreditmemoList
     */
    protected $creditmemoList;

    /**
     * @var CreditmemoCommentsList
     */
    protected $creditmemoCommentsList;

    /**
     * @param CreditmemoGet $creditmemoGet
     * @param CreditmemoList $creditmemoList
     * @param CreditmemoCommentsList $creditmemoCommentsList
     */
    public function __construct(
        CreditmemoGet $creditmemoGet,
        CreditmemoList $creditmemoList,
        CreditmemoCommentsList $creditmemoCommentsList
    ) {
        $this->creditmemoGet = $creditmemoGet;
        $this->creditmemoList = $creditmemoList;
        $this->creditmemoCommentsList = $creditmemoCommentsList;
    }

    /**
     * @param int $id
     * @return \Magento\Sales\Service\V1\Data\Creditmemo
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id)
    {
        return $this->creditmemoGet->invoke($id);
    }

    /**
     * @param SearchCriteria $searchCriteria
     * @return \Magento\Framework\Service\V1\Data\SearchResults
     */
    public function search(SearchCriteria $searchCriteria)
    {
        return $this->creditmemoList->invoke($searchCriteria);
    }

    /**
     * @param int $id
     * @return \Magento\Sales\Service\V1\Data\CommentSearchResults
     */
    public function commentsList($id)
    {
        return $this->creditmemoCommentsList->invoke($id);
    }
}
