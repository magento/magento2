<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\View\Element;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\BookmarkManagementInterface;

class BookmarkContext implements BookmarkContextInterface
{
    /**
     * @var BookmarkManagementInterface
     */
    private BookmarkManagementInterface $bookmarkManagement;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var ContextInterface
     */
    private ContextInterface $context;

    /**
     * @var array|null
     */
    private ?array $bookmarkFilterData = null;

    /**
     * @var bool
     */
    private bool $bookmarkAvailable = false;

    /**
     * BookmarkContext constructor.
     *
     * @param ContextInterface $context
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param RequestInterface $request
     */
    public function __construct(
        ContextInterface $context,
        BookmarkManagementInterface $bookmarkManagement,
        RequestInterface $request
    ) {
        $this->context = $context;
        $this->bookmarkManagement = $bookmarkManagement;
        $this->request = $request;
    }

    /**
     * Prepare filter data from bookmarks
     *
     * @return array
     */
    private function getFilterDataFromBookmark(): array
    {
        if ($this->bookmarkFilterData === null) {
            $this->bookmarkFilterData = [];
            $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
                'current',
                $this->context->getNamespace()
            );

            if ($bookmark !== null) {
                $this->bookmarkAvailable = true;
                $bookmarkConfig = $bookmark->getConfig();
                $this->bookmarkFilterData = $bookmarkConfig['current']['filters']['applied'] ?? [];

                $this->preparePagingParams($bookmarkConfig)
                    ->prepareSoringParams($bookmarkConfig);
            }
        }

        return $this->bookmarkFilterData;
    }

    /**
     * Prepare paging params
     *
     * @param array $bookmarkConfig
     * @return BookmarkContext
     */
    private function preparePagingParams(array $bookmarkConfig): BookmarkContext
    {
        $this->request->setParams(
            [
                'paging' => $bookmarkConfig['current']['paging'] ?? [],
                'search' => $bookmarkConfig['current']['search']['value'] ?? ''
            ]
        );
        return $this;
    }

    /**
     * Prepare sorting params
     *
     * @param array $bookmarkConfig
     * @return BookmarkContext
     */
    private function prepareSoringParams(array $bookmarkConfig): BookmarkContext
    {
        $columns = $bookmarkConfig['current']['columns'] ?? [];
        foreach ($columns as $columnName => $columnConfig) {
            if (isset($columnConfig['sorting']) && $columnConfig['sorting'] !== false) {
                $this->request->setParams([
                    'sorting' => [
                        'field' => $columnName,
                        'direction' => $columnConfig['sorting']
                    ]
                ]);
                break;
            }
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFilterData(): array
    {
        $contextFilterData = $this->context->getRequestParam(ContextInterface::FILTER_VAR);
        if ($contextFilterData !== null) {
            return $contextFilterData;
        }

        return $this->getFilterDataFromBookmark();
    }

    /**
     * @inheritDoc
     */
    public function isBookmarkAvailable(): bool
    {
        return $this->bookmarkAvailable;
    }
}
