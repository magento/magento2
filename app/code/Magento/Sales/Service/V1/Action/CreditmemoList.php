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

use Magento\Sales\Service\V1\Data\CreditmemoMapper;
use Magento\Sales\Model\Order\CreditmemoRepository;
use Magento\Framework\Service\V1\Data\SearchCriteria;
use Magento\Sales\Service\V1\Data\CreditmemoSearchResultsBuilder;

/**
 * Class CreditmemoList
 */
class CreditmemoList
{
    /**
     * @var CreditmemoMapper
     */
    protected $creditmemoMapper;

    /**
     * @var CreditmemoRepository
     */
    protected $creditmemoRepository;

    /**
     * @var CreditmemoSearchResultsBuilder
     */
    protected $searchResultsBuilder;

    /**
     * @param CreditmemoRepository $creditmemoRepository
     * @param CreditmemoMapper $creditmemoMapper
     * @param CreditmemoSearchResultsBuilder $searchResultsBuilder
     */
    public function __construct(
        CreditmemoRepository $creditmemoRepository,
        CreditmemoMapper $creditmemoMapper,
        CreditmemoSearchResultsBuilder $searchResultsBuilder
    ) {
        $this->creditmemoRepository = $creditmemoRepository;
        $this->creditmemoMapper = $creditmemoMapper;
        $this->searchResultsBuilder = $searchResultsBuilder;
    }

    /**
     * Invoke CreditmemoList service
     *
     * @param \Magento\Framework\Service\V1\Data\SearchCriteria $searchCriteria
     * @return \Magento\Framework\Service\V1\Data\SearchResults
     */
    public function invoke(SearchCriteria $searchCriteria)
    {
        $creditmemos = [];
        foreach ($this->creditmemoRepository->find($searchCriteria) as $creditmemo) {
            $creditmemos[] = $this->creditmemoMapper->extractDto($creditmemo);
        }

        return $this->searchResultsBuilder->setItems($creditmemos)
            ->setTotalCount(count($creditmemos))
            ->setSearchCriteria($searchCriteria)
            ->create();
    }
}
