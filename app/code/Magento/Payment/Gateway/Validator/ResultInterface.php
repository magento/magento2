<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Validator;

use Magento\Framework\Phrase;

interface ResultInterface
{
    /**
     * Returns validation result
     *
     * @return bool
     */
    public function isValid();

    /**
     * Returns list of fails description
     *
     * @return Phrase[]
     */
    public function getFailsDescription();
}
