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
        $bookmarks = $this->bookmarkManagement->loadByNamespace($this->context->getNameSpace())->getItems();
        foreach ($bookmarks as $bookmark) {
            if ($bookmark->getIdentifier() === 'current') {
                $applied = $bookmark->getConfig()['current']['filters']['applied'];
                if (isset($applied[$this->getName()])) {
                    $blockIds = $applied[$this->getName()];
                }
            }
        }

        foreach ($blockIds as $id) {
            $block = $this->blockRepository->getById($id);
            $options[] = [
                'value' => $id,
                'label' => $block->getTitle(),
                'is_active' => $block->isActive(),
                'optgroup' => false
              ];
        }

        $this->wrappedComponent = $this->uiComponentFactory->create(
            $this->getName(),
            parent::COMPONENT,
            [
                'context' => $this->getContext(),
                'options' => $options
            ]
        );

        $this->wrappedComponent->prepare();
        $jsConfig = array_replace_recursive(
            $this->getJsConfig($this->wrappedComponent),
            $this->getJsConfig($this)
        );
        $this->setData('js_config', $jsConfig);

        $this->setData(
            'config',
            array_replace_recursive(
                (array)$this->wrappedComponent->getData('config'),
                (array)$this->getData('config')
            )
        );

        $this->applyFilter();

        parent::prepare();
    }
}
