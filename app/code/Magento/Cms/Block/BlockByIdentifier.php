<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Block;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Cms\Model\Block as BlockModel;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * This class is replacement of \Magento\Cms\Block\Block, that accepts only `string` identifier of CMS Block
 */
class BlockByIdentifier extends AbstractBlock implements IdentityInterface
{
    public const CACHE_KEY_PREFIX = 'CMS_BLOCK';

    /**
     * @var GetBlockByIdentifierInterface
     */
    private $blockByIdentifier;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var BlockInterface
     */
    private $cmsBlock;

    /**
     * @param GetBlockByIdentifierInterface $blockByIdentifier
     * @param StoreManagerInterface $storeManager
     * @param FilterProvider $filterProvider
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        GetBlockByIdentifierInterface $blockByIdentifier,
        StoreManagerInterface $storeManager,
        FilterProvider $filterProvider,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->blockByIdentifier = $blockByIdentifier;
        $this->storeManager = $storeManager;
        $this->filterProvider = $filterProvider;
    }

    /**
     * @inheritDoc
     */
    protected function _toHtml(): string
    {
        try {
            return $this->filterOutput(
                $this->getCmsBlock()->getContent()
            );
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * Returns the value of `identifier` injected in `<block>` definition
     *
     * @return string|null
     */
    private function getIdentifier(): ?string
    {
        return $this->getData('identifier') ?: null;
    }

    /**
     * Filters the Content
     *
     * @param string $content
     * @return string
     * @throws NoSuchEntityException
     */
    private function filterOutput(string $content): string
    {
        return $this->filterProvider->getBlockFilter()
            ->setStoreId($this->getCurrentStoreId())
            ->filter($content);
    }

    /**
     * Loads the CMS block by `identifier` provided as an argument
     *
     * @return BlockInterface|BlockModel
     * @throws \InvalidArgumentException
     * @throws NoSuchEntityException
     */
    private function getCmsBlock(): BlockInterface
    {
        if (!$this->getIdentifier()) {
            throw new \InvalidArgumentException('Expected value of `identifier` was not provided');
        }

        if (null === $this->cmsBlock) {
            $this->cmsBlock = $this->blockByIdentifier->execute(
                (string)$this->getIdentifier(),
                $this->getCurrentStoreId()
            );

            if (!$this->cmsBlock->isActive()) {
                throw new NoSuchEntityException(
                    __('The CMS block with identifier "%identifier" is not enabled.', $this->getIdentifier())
                );
            }
        }

        return $this->cmsBlock;
    }

    /**
     * Returns the current Store ID
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCurrentStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }

    /**
     * Returns array of Block Identifiers used to determine Cache Tags
     *
     * This implementation supports different CMS blocks caching having the same identifier,
     * resolving the bug introduced in scope of \Magento\Cms\Block\Block
     *
     * @return string[]
     */
    public function getIdentities(): array
    {
        if (!$this->getIdentifier()) {
            return [];
        }

        $identities = [
            self::CACHE_KEY_PREFIX . '_' . $this->getIdentifier(),
            self::CACHE_KEY_PREFIX . '_' . $this->getIdentifier() . '_' . $this->getCurrentStoreId()
        ];

        try {
            $cmsBlock = $this->getCmsBlock();
            if ($cmsBlock instanceof IdentityInterface) {
                $identities = array_merge($identities, $cmsBlock->getIdentities());
            }
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (NoSuchEntityException $e) {
        }

        return $identities;
    }
}
