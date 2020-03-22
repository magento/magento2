<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\App\Action\Plugin;

use Magento\Backend\App\ActionInterface;
use Magento\Framework\View\DesignLoader;

/**
 * Workaround to load Design before Backend Action dispatch.
 *
 * @FIXME Remove when \Magento\Backend\App\AbstractAction::dispatch refactored.
 */
class BackendActionLoadDesignPlugin
{
    /**
     * @var DesignLoader
     */
    private $designLoader;

    /**
     * @param DesignLoader $designLoader
     */
    public function __construct(DesignLoader $designLoader)
    {
        $this->designLoader = $designLoader;
    }

    /**
     * Initiates design before dispatching Backend Actions.
     *
     * @param ActionInterface $backendAction
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(ActionInterface $backendAction)
    {
        $this->designLoader->load();
    }
}
