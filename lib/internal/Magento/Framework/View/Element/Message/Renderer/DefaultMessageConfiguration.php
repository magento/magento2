<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Message\Factory;
use Magento\Framework\Exception\NotFoundException;

class DefaultMessageConfiguration implements MessageConfigurationInterface
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
    public function generateMessage(\Exception $exception)
    {
        if ($exception instanceof \Exception) {
            return $this->messageFactory->create(MessageInterface::TYPE_ERROR)
                ->setText($exception->getMessage());
        } else {
            throw new NotFoundException(
                new \Magento\Framework\Phrase("Exception instance doesn't match %1 type", [\Exception::class])
            );
        }
    }
}
