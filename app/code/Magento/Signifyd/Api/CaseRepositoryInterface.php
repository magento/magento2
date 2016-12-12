<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Api;

use Magento\Framework\Api\SearchCriteria;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Api\Data\CaseSearchResultInterface;

/**
 * Signifyd Case repository interface
 *
 * @api
 */
interface CaseRepositoryInterface
{
    /**
     * Saves case entity
     * @param CaseInterface $case
     * @return CaseInterface
     */
    public function save(CaseInterface $case);

    /**
     * Gets case entity by order id
     * @param int $orderId
     * @return CaseInterface
     */
    public function getById($orderId);
}
