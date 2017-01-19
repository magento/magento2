<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Request;

/**
 * Collects information about order and build array with parameters required by Signifyd API
 *
 * @see https://www.signifyd.com/docs/api/#/reference/cases/create-a-case
 */
interface CreateCaseBuilderInterface
{
    /**
     * Returns params for Case creation request
     *
     * @param int $orderId
     * @return array
     */
    public function build($orderId);
}
