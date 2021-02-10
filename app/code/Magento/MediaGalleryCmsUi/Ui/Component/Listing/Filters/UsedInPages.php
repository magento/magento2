<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryCmsUi\Ui\Component\Listing\Filters;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Filters\Type\Select;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Cms\Api\PageRepositoryInterface;

/**
 * Used in pages filter
 */
class UsedInPages extends Select
{
    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterBuilder $filterBuilder
     * @param FilterModifier $filterModifier
     * @param OptionSourceInterface $optionsProvider
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param PageRepositoryInterface $pageRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterBuilder $filterBuilder,
        FilterModifier $filterModifier,
        OptionSourceInterface $optionsProvider = null,
        BookmarkManagementInterface $bookmarkManagement,
        PageRepositoryInterface $pageRepository,
        array $components = [],
        array $data = []
    ) {
        $this->uiComponentFactory = $uiComponentFactory;
        $this->filterBuilder = $filterBuilder;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $filterBuilder,
            $filterModifier,
            $optionsProvider,
            $components,
            $data
        );
        $this->bookmarkManagement = $bookmarkManagement;
        $this->pageRepository = $pageRepository;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $options = [];
        $pageIds = [];
        $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            'current',
            $this->context->getNameSpace()
        );
        if ($bookmark === null) {
            parent::prepare();
             return;
        }

        $applied = $bookmark->getConfig()['current']['filters']['applied'];

        if (isset($applied[$this->getName()])) {
            $pageIds = $applied[$this->getName()];
        }

        foreach ($pageIds as $id) {
            try {
                $page = $this->pageRepository->getById($id);
                $options[] = [
                    'value' => $id,
                    'label' => $page->getTitle(),
                    'is_active' => $page->isActive(),
                    'optgroup' => false
                ];
            } catch (\Exception $e) {
                continue;
            }
        }
        $this->optionsProvider = $options;
        parent::prepare();
    }
}
