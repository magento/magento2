<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Model\Quote;

/**
 * Class Msrp
 */
class Msrp
{
    /**
     * @var array
     */
    protected $canApplyMsrpData = [];

    /**
     * @param int $quoteId
     * @param bool $canApply
     * @return $this
     */
    public function setCanApplyMsrp($quoteId, $canApply)
    {
        $this->canApplyMsrpData[$quoteId] = (bool)$canApply;
        return $this;
    }

    /**
     * @param int $quoteId
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCanApplyMsrp($quoteId)
    {
        if (isset($this->canApplyMsrpData[$quoteId])) {
            return (bool)$this->canApplyMsrpData[$quoteId];
        }
        return false;
    }
}
