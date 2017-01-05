<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

/**
 * Common abstraction to perform updating operations with Signifyd case entity.
 */
interface CaseUpdatingServiceInterface
{
    /**
     * Updates Signifyd Case entity by received data.
     *
     * @param array $data
     * @return void
     */
    public function update(array $data);
}
