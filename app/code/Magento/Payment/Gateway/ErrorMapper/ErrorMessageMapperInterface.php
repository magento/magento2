<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Gateway\ErrorMapper;

use Magento\Framework\Phrase;

/**
 * Interface to provide customization for payment validation errors.
 *
 * @api
 */
interface ErrorMessageMapperInterface
{
    /**
     * Returns customized error message by provided code.
     * If message not found `null` will be returned.
     *
     * @param string $code
     * @return Phrase|null
     */
    public function getMessage(string $code);
}
