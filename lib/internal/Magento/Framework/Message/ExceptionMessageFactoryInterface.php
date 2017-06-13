<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Message;

use Magento\Framework\Exception\NotFoundException;

interface ExceptionMessageFactoryInterface
{
    /**
     * Creates error message based on Exception type and the data it contains
     *
     * @param \Exception $exception
     * @param string $type
     * @return MessageInterface
     * @throws NotFoundException
     */
    public function createMessage(\Exception $exception, $type = MessageInterface::TYPE_ERROR);
}
