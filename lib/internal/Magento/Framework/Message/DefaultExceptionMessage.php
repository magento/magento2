<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Message;

use Magento\Framework\Exception\NotFoundException;

class DefaultExceptionMessage implements ExceptionMessageInterface
{
    /** @var Factory */
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
        } else {
            throw new NotFoundException(
                new \Magento\Framework\Phrase("Exception instance doesn't match %1 type", [\Exception::class])
            );
        }
    }
}
