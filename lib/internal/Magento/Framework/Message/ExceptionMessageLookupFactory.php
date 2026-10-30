<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Message;

class ExceptionMessageLookupFactory implements ExceptionMessageFactoryInterface
{
    /**
     * @var ExceptionMessageFactoryPool
     */
    private $exceptionMessageFactoryPool;

    /**
     * @param ExceptionMessageFactoryPool $exceptionMessageFactoryPool
     */
    public function __construct(ExceptionMessageFactoryPool $exceptionMessageFactoryPool)
    {
        $this->exceptionMessageFactoryPool = $exceptionMessageFactoryPool;
    }

    /**
     * @inheritdoc
     */
    public function createMessage(\Exception $exception, $type = MessageInterface::TYPE_ERROR)
    {
        $messageGenerator = $this->exceptionMessageFactoryPool->getMessageFactory($exception);
        return $messageGenerator->createMessage($exception, $type);
    }
}
