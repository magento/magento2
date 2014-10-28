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

use Magento\Framework\Service\V1\Data\SearchCriteria;
use Magento\Sales\Model\Order\Payment\TransactionRepository;

class TransactionRead implements TransactionReadInterface
{
    /**
     * @var Data\TransactionMapper
     */
    private $transactionMapper;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * @var Data\TransactionSearchResultsBuilder
     */
    private $searchResultsBuilder;

    /**
     * @param Data\TransactionMapper $transactionMapper
     * @param TransactionRepository $transactionRepository
     * @param Data\TransactionSearchResultsBuilder $searchResultsBuilder
     */
    public function __construct(
        Data\TransactionMapper $transactionMapper,
        TransactionRepository $transactionRepository,
        Data\TransactionSearchResultsBuilder $searchResultsBuilder
    ) {
        $this->transactionMapper = $transactionMapper;
        $this->transactionRepository = $transactionRepository;
        $this->searchResultsBuilder = $searchResultsBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $transaction = $this->transactionRepository->get($id);
        return $this->transactionMapper->extractDto($transaction);
    }

    /**
     * {@inheritdoc}
     */
    public function search(SearchCriteria $searchCriteria)
    {
        $transactions = [];
        foreach ($this->transactionRepository->find($searchCriteria) as $transaction) {
            $transactions[] = $this->transactionMapper->extractDto($transaction, true);
        }
        return $this->searchResultsBuilder->setItems($transactions)
            ->setTotalCount(count($transactions))
            ->setSearchCriteria($searchCriteria)
            ->create();
    }
}
