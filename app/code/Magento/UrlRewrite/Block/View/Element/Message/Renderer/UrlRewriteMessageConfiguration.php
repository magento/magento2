<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Block\View\Element\Message\Renderer;

use Magento\Framework\View\Element\Message\Renderer\MessageConfigurationInterface;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Message\Factory;

class UrlRewriteMessageConfiguration implements MessageConfigurationInterface
{
    const ADD_URL_DUPLICATE_MESSAGE = 'addUrlDuplicateMessage';

    const EXCEPTION_CLASS = UrlAlreadyExistsException::class;

    /** @var Factory */
    private $messageFactory;

    /**
     * UrlRewriteExceptionRendererIdentifier constructor.
     * @param Factory $messageFactory
     */
    public function __construct(Factory $messageFactory)
    {
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param UrlAlreadyExistsException $exception
     * @return MessageInterface
     */
    public function createMessage($exception)
    {
        return $this->messageFactory->create(MessageInterface::TYPE_ERROR)
            ->setIdentifier(
                empty(self::ADD_URL_DUPLICATE_MESSAGE)
                    ? MessageInterface::DEFAULT_IDENTIFIER
                    : self::ADD_URL_DUPLICATE_MESSAGE
            )->setData(['urls' => $exception->getUrls()]);
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return self::ADD_URL_DUPLICATE_MESSAGE;
    }

    /**
     * @return string
     */
    public function getExceptionClass()
    {
        return self::EXCEPTION_CLASS;
    }
}
