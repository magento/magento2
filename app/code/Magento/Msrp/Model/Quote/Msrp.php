<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Model\Quote;

/**
 * Class Msrp
 * @since 2.0.0
 */
class Msrp
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $canApplyMsrpData = [];

    /**
     * @param int $quoteId
     * @param bool $canApply
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getCanApplyMsrp($quoteId)
    {
        if (isset($this->canApplyMsrpData[$quoteId])) {
            return (bool)$this->canApplyMsrpData[$quoteId];
        }
        return false;
    }
}
