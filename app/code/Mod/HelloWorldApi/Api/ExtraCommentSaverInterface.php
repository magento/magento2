<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldApi\Api;

use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Extra Comment saver service interface.
 * @api
 */
interface ExtraCommentSaverInterface
{
    /**
     * Save extra comment.
     *
     * @param int $customerId
     * @param string $productSku
     * @param string $extraComment
     * @return bool
     * @throws CouldNotSaveException
     */
    public function execute(int $customerId, string $productSku, string $extraComment): bool;
}
