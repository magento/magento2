<?php
/**
 * Plugin for \Magento\Framework\Mview\View\StateInterface model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Model\Indexer\Category\Product\Plugin;

/**
 * Class \Magento\Catalog\Model\Indexer\Category\Product\Plugin\MviewState
 *
 * @since 2.0.0
 */
class MviewState
{
    /**
     * @var \Magento\Framework\Mview\View\StateInterface
     * @since 2.0.0
     */
    protected $state;

    /**
     * @var \Magento\Framework\Mview\View\ChangelogInterface
     * @since 2.0.0
     */
    protected $changelog;

    /**
     * Related indexers IDs
     *
     * @var int[]
     * @since 2.0.0
     */
    protected $viewIds = [
        \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID,
        \Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID,
    ];

    /**
     * @param \Magento\Framework\Mview\View\StateInterface $state
     * @param \Magento\Framework\Mview\View\ChangelogInterface $changelog
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Mview\View\StateInterface $state,
        \Magento\Framework\Mview\View\ChangelogInterface $changelog
    ) {
        $this->state = $state;
        $this->changelog = $changelog;
    }

    /**
     * Synchronize status for view
     *
     * @param \Magento\Framework\Mview\View\StateInterface $state
     * @return \Magento\Framework\Mview\View\StateInterface
     * @since 2.0.0
     */
    public function afterSetStatus(\Magento\Framework\Mview\View\StateInterface $state)
    {
        if (in_array($state->getViewId(), $this->viewIds)) {
            $viewId = $state->getViewId() ==
                \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID ? \Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID : \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID;

            $relatedViewState = $this->state->loadByView($viewId);

            // if equals nothing to change
            if ($relatedViewState->getMode() == \Magento\Framework\Mview\View\StateInterface::MODE_DISABLED ||
                $state->getStatus() == $relatedViewState->getStatus()
            ) {
                return $state;
            }

            // suspend
            if ($state->getStatus() == \Magento\Framework\Mview\View\StateInterface::STATUS_SUSPENDED) {
                $relatedViewState->setStatus(\Magento\Framework\Mview\View\StateInterface::STATUS_SUSPENDED);
                $relatedViewState->setVersionId($this->changelog->setViewId($viewId)->getVersion());
                $relatedViewState->save();
            } else {
                if ($relatedViewState->getStatus() == \Magento\Framework\Mview\View\StateInterface::STATUS_SUSPENDED) {
                    $relatedViewState->setStatus(\Magento\Framework\Mview\View\StateInterface::STATUS_IDLE);
                    $relatedViewState->save();
                }
            }
        }

        return $state;
    }
}
