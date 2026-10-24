<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class QuoteException extends GraphQlInputException
{
    /**
     * Get error category
     *
     * @return array
     */
    public function getExtensions(): array
    {
        $extensions['category'] = $this->getCategory();
        if ($this->code) {
            $extensions['error_code'] = ErrorMapper::MESSAGE_CODE_IDS[$this->code] ?? ErrorMapper::ERROR_UNDEFINED;
        }

        return $extensions;
    }
}
