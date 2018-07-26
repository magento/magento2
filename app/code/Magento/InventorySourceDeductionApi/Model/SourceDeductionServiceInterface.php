<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceDeductionApi\Model;

/**
 * Process source deduction
 *
 * @api
 */
interface SourceDeductionServiceInterface
{
    /**
     * @param SourceDeductionRequestInterface $sourceDeductionRequest
     * @return void
     */
    public function execute(SourceDeductionRequestInterface $sourceDeductionRequest): void;
}
