<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Model\Message;

use Magento\Framework\Message\ExceptionMessageFactoryInterface;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Message\Factory;
use Magento\Framework\Exception\NotFoundException;

class UrlRewriteExceptionMessageFactory implements ExceptionMessageFactoryInterface
{
    const ADD_URL_DUPLICATE_MESSAGE = 'addUrlDuplicateMessage';

    const EXCEPTION_CLASS = UrlAlreadyExistsException::class;

    /** @var Factory */
    private $messageFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @param Factory $messageFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(Factory $messageFactory, \Magento\Framework\UrlInterface $urlBuilder)
    {
        $this->messageFactory = $messageFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritdoc
     */
    public function createMessage(\Exception $exception, $type = MessageInterface::TYPE_ERROR)
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
                ->setIdentifier(self::ADD_URL_DUPLICATE_MESSAGE)
                ->setText($exception->getMessage())
                ->setData(['urls' => $generatedUrls]);
        }
        throw new NotFoundException(
            __('Exception instance doesn\'t match %1 type', UrlAlreadyExistsException::class)
        );
    }
}
