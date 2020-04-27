<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Request;

/**
 * Collects information about order and build array with parameters required by Signifyd API
 *
 * @see https://www.signifyd.com/docs/api/#/reference/cases/create-a-case
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
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
