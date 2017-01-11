<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Guarantee;

use Magento\Signifyd\Model\CaseManagement;
use Magento\Signifyd\Api\Data\CaseInterface;

class SubmitEligible
{
    /**
     * @var CaseManagement
     */
    private $caseManagement;

    /**
     * SubmitEligible constructor.
     *
     * @param CaseManagement $caseManagement
     */
    public function __construct(
        CaseManagement $caseManagement
    ) {
        $this->caseManagement = $caseManagement;
    }

    /**
     * Checks if Guarantee submit is applicable for order.
     *
     * @param integer $orderId
     * @return bool
     */
    public function check($orderId)
    {
        $case = $this->getCaseEntity($orderId);

        return true;
    }

    /**
     * Retrieves case entity by order id.
     *
     * @param integer $orderId
     * @return CaseInterface|null
     */
    private function getCaseEntity($orderId)
    {
        return $this->caseManagement->getByOrderId($orderId);
    }
}
