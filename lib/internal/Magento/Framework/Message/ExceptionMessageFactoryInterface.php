<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Message;

use Magento\Framework\Exception\RuntimeException;

/**
 * Interface \Magento\Framework\Message\ExceptionMessageFactoryInterface
 *
 * @api
 */
interface ExceptionMessageFactoryInterface
{
    /**
     * Creates error message based on Exception type and the data it contains
     *
     * @param \Exception $exception
     * @param string $type
     * @return MessageInterface
     * @throws RuntimeException
     */
    public function createMessage(\Exception $exception, $type = MessageInterface::TYPE_ERROR);
}
