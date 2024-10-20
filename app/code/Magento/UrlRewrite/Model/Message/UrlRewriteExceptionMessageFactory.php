<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Model\Message;

use Exception;
use Magento\Framework\Message\ExceptionMessageFactoryInterface;
use Magento\Framework\UrlInterface;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Message\Factory;
use Magento\Framework\Exception\RuntimeException;

class UrlRewriteExceptionMessageFactory implements ExceptionMessageFactoryInterface
{
    const URL_DUPLICATE_MESSAGE_MAP_ID = 'urlDuplicateMessageMapId';

    /**
     * @param Factory $messageFactory
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        private readonly Factory $messageFactory,
        private readonly UrlInterface $urlBuilder
    ) {
    }

    /**
     * @inheritdoc
     */
    public function createMessage(Exception $exception, $type = MessageInterface::TYPE_ERROR)
    {
        if ($exception instanceof UrlAlreadyExistsException) {
            $generatedUrls = [];
            $urls = $exception->getUrls();
            if ($urls && is_array($urls)) {
                foreach ($urls as $id => $url) {
                    $adminEditUrl = $this->urlBuilder->getUrl(
                        'adminhtml/url_rewrite/edit',
                        ['id' => $id]
                    );
                    $generatedUrls[$adminEditUrl] = $url['request_path'];
                }
            }
            return $this->messageFactory->create($type)
                ->setIdentifier(self::URL_DUPLICATE_MESSAGE_MAP_ID)
                ->setText($exception->getMessage())
                ->setData(['urls' => $generatedUrls]);
        }
        throw new RuntimeException(
            __('Exception instance doesn\'t match %1 type', UrlAlreadyExistsException::class)
        );
    }
}
