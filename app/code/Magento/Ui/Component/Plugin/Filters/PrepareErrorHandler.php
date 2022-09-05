<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Component\Plugin\Filters;

use Magento\Framework\View\Element\UiComponent\DataProvider\Sanitizer;
use Magento\Ui\Component\Filters\Type\AbstractFilter;
use Magento\Ui\View\Element\BookmarkContextProviderInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class PrepareErrorHandler
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Sanitizer
     */
    private Sanitizer $sanitizer;

    /**
     * @var BookmarkContextProviderInterface
     */
    private BookmarkContextProviderInterface $bookmarkContextProvider;

    /**
     * PrepareErrorHandler constructor.
     *
     * @param BookmarkContextProviderInterface $bookmarkContextProvider
     * @param Sanitizer $sanitizer
     * @param LoggerInterface $logger
     */
    public function __construct(
        BookmarkContextProviderInterface $bookmarkContextProvider,
        Sanitizer $sanitizer,
        LoggerInterface $logger
    ) {
        $this->bookmarkContextProvider = $bookmarkContextProvider;
        $this->sanitizer = $sanitizer;
        $this->logger = $logger;
    }

    /**
     * Add error config if filter prepare throw exception
     *
     * @param AbstractFilter $subject
     * @param callable $proceed
     */
    public function aroundPrepare(AbstractFilter $subject, callable $proceed)
    {
        try {
            $proceed();
        } catch (Throwable $e) {
            $this->logger->critical($e->getMessage());

            $dataProvider = $subject->getContext()->getDataProvider();
            $config = array_replace(
                $dataProvider->getConfigData(),
                [
                    'lastError' => true
                ]
            );
            $dataProvider->setConfigData($this->sanitizer->sanitize($config));
        }
    }
}
