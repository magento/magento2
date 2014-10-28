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

use Magento\Sales\Model\Order\ShipmentRepository;
use Magento\Sales\Service\V1\Data\ShipmentMapper;
use Magento\Sales\Service\V1\Data\ShipmentSearchResultsBuilder;
use Magento\Framework\Service\V1\Data\SearchCriteria;

/**
 * Class ShipmentList
 */
class ShipmentList
{
    /**
     * @var ShipmentRepository
     */
    protected $shipmentRepository;

    /**
     * @var ShipmentMapper
     */
    protected $shipmentMapper;

    /**
     * @var ShipmentSearchResultsBuilder
     */
    protected $searchResultsBuilder;

    /**
     * @param ShipmentRepository $shipmentRepository
     * @param ShipmentMapper $shipmentMapper
     * @param ShipmentSearchResultsBuilder $searchResultsBuilder
     */
    public function __construct(
        ShipmentRepository $shipmentRepository,
        ShipmentMapper $shipmentMapper,
        ShipmentSearchResultsBuilder $searchResultsBuilder
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->shipmentMapper = $shipmentMapper;
        $this->searchResultsBuilder = $searchResultsBuilder;
    }

    /**
     * Invoke ShipmentList service
     *
     * @param SearchCriteria $searchCriteria
     * @return \Magento\Framework\Service\V1\Data\SearchResults
     */
    public function invoke(SearchCriteria $searchCriteria)
    {
        $shipments = [];
        foreach ($this->shipmentRepository->find($searchCriteria) as $shipment) {
            $shipments[] = $this->shipmentMapper->extractDto($shipment);
        }
        return $this->searchResultsBuilder->setItems($shipments)
            ->setTotalCount(count($shipments))
            ->setSearchCriteria($searchCriteria)
            ->create();
    }
}
