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
namespace Magento\Sales\Model\Service;

use Magento\Sales\Api\InvoiceManagementInterface;

/**
 * Class InvoiceService
 */
class InvoiceService implements InvoiceManagementInterface
{
    /**
     * Repository
     *
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $repository;

    /**
     * Repository
     *
     * @var \Magento\Sales\Api\InvoiceCommentRepositoryInterface
     */
    protected $commentRepository;

    /**
     * Search Criteria Builder
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * Filter Builder
     *
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Invoice Notifier
     *
     * @var \Magento\Sales\Model\Order\InvoiceNotifier
     */
    protected $invoiceNotifier;

    /**
     * Constructor
     *
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $repository
     * @param \Magento\Sales\Api\InvoiceCommentRepositoryInterface $commentRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Sales\Model\Order\InvoiceNotifier $notifier
     */
    public function __construct(
        \Magento\Sales\Api\InvoiceRepositoryInterface $repository,
        \Magento\Sales\Api\InvoiceCommentRepositoryInterface $commentRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Sales\Model\Order\InvoiceNotifier $notifier
    ) {
        $this->repository = $repository;
        $this->commentRepository = $commentRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->invoiceNotifier = $notifier;
    }

    /**
     * Set invoice capture
     *
     * @param int $id
     * @return string
     */
    public function setCapture($id)
    {
        return (bool)$this->repository->get($id)->capture();
    }

    /**
     * Returns list of comments attached to invoice
     * @param int $id
     * @return \Magento\Sales\Api\Data\InvoiceSearchResultInterface
     */
    public function getCommentsList($id)
    {
        $this->criteriaBuilder->addFilter(
            ['eq' => $this->filterBuilder->setField('parent_id')->setValue($id)->create()]
        );
        $criteria = $this->criteriaBuilder->create();
        return $this->commentRepository->getList($criteria);
    }

    /**
     * Notify user
     *
     * @param int $id
     * @return bool
     */
    public function notify($id)
    {
        $invoice = $this->repository->get($id);
        return $this->invoiceNotifier->notify($invoice);
    }

    /**
     * Set invoice void
     *
     * @param int $id
     * @return bool
     */
    public function setVoid($id)
    {
        return (bool)$this->repository->get($id)->void();
    }
}
