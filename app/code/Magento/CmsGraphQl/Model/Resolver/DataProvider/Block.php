<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver\DataProvider;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Widget\Model\Template\FilterEmulate;
use Magento\Framework\App\State;

/**
 * Cms block data provider
 */
class Block
{
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var FilterEmulate
     */
    private $widgetFilter;

    /**
     * @var State
     */
    private $state;

    /**
     * @param BlockRepositoryInterface $blockRepository
     * @param FilterEmulate $widgetFilter
     * @param State $state
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        FilterEmulate $widgetFilter,
        State $state
    ) {
        $this->blockRepository = $blockRepository;
        $this->widgetFilter = $widgetFilter;
        $this->state = $state;
    }

    /**
     * Get block data
     *
     * @param string $blockIdentifier
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(string $blockIdentifier): array
    {
        $block = $this->blockRepository->getById($blockIdentifier);

        if (false === $block->isActive()) {
            throw new NoSuchEntityException(
                __('The CMS block with the "%1" ID doesn\'t exist.', $blockIdentifier)
            );
        }

        $renderedContent = $this->state->emulateAreaCode(
            'frontend',
            [$this, 'getRenderedBlockContent'],
            [$block->getContent()]
        );

        $blockData = [
            BlockInterface::BLOCK_ID => $block->getId(),
            BlockInterface::IDENTIFIER => $block->getIdentifier(),
            BlockInterface::TITLE => $block->getTitle(),
            BlockInterface::CONTENT => $renderedContent,
        ];
        return $blockData;
    }

    /**
     * @param string $blockContent
     *
     * @return string
     */
    public function getRenderedBlockContent(string $blockContent) : string
    {
        return $this->widgetFilter->filter($blockContent);
    }
}
