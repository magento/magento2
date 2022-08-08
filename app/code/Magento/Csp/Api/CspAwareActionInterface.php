<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Api;

use Magento\Framework\App\ActionInterface;

/**
 * Interface for controllers that can provide route-specific CSPs.
 *
 * @api
 */
interface CspAwareActionInterface extends ActionInterface
{
    /**
     * Return CSPs that will be applied to current route (page).
     *
     * The array returned will be used as is so if you need to keep policies that have been already applied they need
     * to be included in the resulting array.
     *
     * @param \Magento\Csp\Api\Data\PolicyInterface[] $appliedPolicies
     * @return \Magento\Csp\Api\Data\PolicyInterface[]
     */
    public function modifyCsp(array $appliedPolicies): array;
}
