<?php
/**
 * Plugin for \Magento\Framework\Mview\View\StateInterface model
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Plugin;

class MviewState
{
    /**
     * @var \Magento\Framework\Mview\View\StateInterface
     */
    protected $state;

    /**
     * @var \Magento\Framework\Mview\View\ChangelogInterface
     */
    protected $changelog;

    /**
     * Related indexers IDs
     *
     * @var int[]
     */
    protected $viewIds = array(
        \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID,
        \Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID
    );

    /**
     * @param \Magento\Framework\Mview\View\StateInterface $state
     * @param \Magento\Framework\Mview\View\ChangelogInterface $changelog
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
