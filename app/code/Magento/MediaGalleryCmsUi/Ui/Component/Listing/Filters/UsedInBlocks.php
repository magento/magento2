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
use Magento\Cms\Api\BlockRepositoryInterface;

/**
 * Used in blocks filter
 */
class UsedInBlocks extends Select
{
    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterBuilder $filterBuilder
     * @param FilterModifier $filterModifier
     * @param OptionSourceInterface $optionsProvider
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param BlockRepositoryInterface $blockRepository
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
        BlockRepositoryInterface $blockRepository,
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
        $this->blockRepository = $blockRepository;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $options = [];
        $blockIds = [];
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
            $blockIds = $applied[$this->getName()];
        }

        foreach ($blockIds as $id) {
            try {
                $block = $this->blockRepository->getById($id);
                $options[] = [
                    'value' => $id,
                    'label' => $block->getTitle(),
                    'is_active' => $block->isActive(),
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
