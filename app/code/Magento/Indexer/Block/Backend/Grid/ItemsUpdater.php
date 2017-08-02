<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Block\Backend\Grid;

/**
 * Class \Magento\Indexer\Block\Backend\Grid\ItemsUpdater
 *
 * @since 2.0.0
 */
class ItemsUpdater implements \Magento\Framework\View\Layout\Argument\UpdaterInterface
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     * @since 2.0.0
     */
    protected $authorization;

    /**
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * Remove massaction items in case they disallowed for user
     * @param mixed $argument
     * @return mixed
     * @since 2.0.0
     */
    public function update($argument)
    {
        if (false === $this->authorization->isAllowed('Magento_Indexer::changeMode')) {
            unset($argument['change_mode_onthefly']);
            unset($argument['change_mode_changelog']);
        }
        return $argument;
    }
}
