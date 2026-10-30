<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Message;

use Magento\Framework\Exception\RuntimeException;

class ExceptionMessageFactory implements ExceptionMessageFactoryInterface
{
    /**
     * @var \Magento\Framework\Message\Factory
     */
    private $messageFactory;

    /**
     * @param Factory $messageFactory
     */
    public function __construct(Factory $messageFactory)
    {
        $this->messageFactory = $messageFactory;
    }

    /**
     * @inheritdoc
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
