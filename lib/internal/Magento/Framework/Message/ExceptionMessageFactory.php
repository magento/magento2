<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Message;

use Magento\Framework\Exception\RuntimeException;

/**
 * Class \Magento\Framework\Message\ExceptionMessageFactory
 *
 * @since 2.2.0
 */
class ExceptionMessageFactory implements ExceptionMessageFactoryInterface
{
    /**
     * @var \Magento\Framework\Message\Factory
     * @since 2.2.0
     */
    private $messageFactory;

    /**
     * @param Factory $messageFactory
     * @since 2.2.0
     */
    public function __construct(Factory $messageFactory)
    {
        $this->messageFactory = $messageFactory;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function createMessage(\Exception $exception, $type = MessageInterface::TYPE_ERROR)
    {
        if ($exception instanceof \Exception) {
            return $this->messageFactory->create($type)
                ->setText($exception->getMessage());
        }
        throw new RuntimeException(
            new \Magento\Framework\Phrase("Exception instance doesn't match %1 type", [\Exception::class])
        );
    }
}
