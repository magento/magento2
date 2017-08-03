<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\CaseServices;

use Magento\Signifyd\Api\Data\CaseInterface;

/**
 * Common abstraction to perform updating operations with Signifyd case entity.
 * @since 2.2.0
 */
interface UpdatingServiceInterface
{
    /**
     * Updates Signifyd Case entity by received data.
     *
     * @param CaseInterface $case
     * @param array $data
     * @return void
     * @since 2.2.0
     */
    public function update(CaseInterface $case, array $data);
}
