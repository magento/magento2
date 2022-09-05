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

class SetFirstLoadFlag
{
    /**
     * @var BookmarkContextProviderInterface
     */
    private BookmarkContextProviderInterface $bookmarkContextProvider;

    /**
     * @var Sanitizer
     */
    private Sanitizer $sanitizer;

    /**
     * @param BookmarkContextProviderInterface $bookmarkContextProvider
     * @param Sanitizer $sanitizer
     */
    public function __construct(
        BookmarkContextProviderInterface $bookmarkContextProvider,
        Sanitizer $sanitizer
    ) {
        $this->bookmarkContextProvider = $bookmarkContextProvider;
        $this->sanitizer = $sanitizer;
    }

    /**
     * Set first load flag for prevent trigger grid ajax reload if bookmarks available
     *
     * @param AbstractFilter $subject
     */
    public function beforePrepare(AbstractFilter $subject): void
    {
        $bookmarkContext = $this->bookmarkContextProvider->getByUiContext($subject->getContext());
        if ($bookmarkContext->isBookmarkAvailable()) {
            $dataProvider = $subject->getContext()->getDataProvider();
            $config = array_replace(
                $dataProvider->getConfigData(),
                [
                    'firstLoad' => false
                ]
            );
            $dataProvider->setConfigData($this->sanitizer->sanitize($config));
        }
    }
}
