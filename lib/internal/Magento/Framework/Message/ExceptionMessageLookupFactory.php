<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Message;

/**
 * Class \Magento\Framework\Message\ExceptionMessageLookupFactory
 *
 * @since 2.2.0
 */
class ExceptionMessageLookupFactory implements ExceptionMessageFactoryInterface
{
    /**
     * @var ExceptionMessageFactoryPool
     * @since 2.2.0
     */
    private $exceptionMessageFactoryPool;

    /**
     * @param ExceptionMessageFactoryPool $exceptionMessageFactoryPool
     * @since 2.2.0
     */
    public function __construct(ExceptionMessageFactoryPool $exceptionMessageFactoryPool)
    {
        $this->exceptionMessageFactoryPool = $exceptionMessageFactoryPool;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function createMessage(\Exception $exception, $type = MessageInterface::TYPE_ERROR)
    {
        $messageGenerator = $this->exceptionMessageFactoryPool->getMessageFactory($exception);
        return $messageGenerator->createMessage($exception, $type);
    }
}
