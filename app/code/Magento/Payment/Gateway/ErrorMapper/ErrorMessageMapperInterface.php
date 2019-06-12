<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
namespace Magento\Payment\Gateway\ErrorMapper;

use Magento\Framework\Phrase;

/**
 * Interface to provide customization for payment validation errors.
<<<<<<< HEAD
=======
 *
 * @api
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
