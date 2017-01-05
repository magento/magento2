<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\DataObject;

/**
 * Common abstraction to perform updating operations with Signifyd case entity.
 */
interface CaseUpdatingServiceInterface
{
    /**
     * Updates Signifyd Case entity by received data.
     *
     * @param DataObject $data
     * @return void
     */
    public function update(DataObject $data);
}
