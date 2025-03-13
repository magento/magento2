<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Plugin;

use Magento\Framework\View\Layout;
use Magento\Persistent\Block\Header\RememberMeInit;
use Magento\Persistent\Helper\Data;
use Magento\Customer\Model\Session;

/**
 * Plugin to add layout handle and block for persistent remember me init
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AddPersistentRememberMeInitPlugin
{
    /**
     * @param Data $persistentData
     * @param Session $customerSession
     */
    public function __construct(
        private readonly Data $persistentData,
        private readonly Session $customerSession
    ) {
    }

    /**
     * Add the RememberMeInit block to the layout.
     *
     * @param Layout $subject
     * @param callable $proceed
     * @return void
     */
    public function aroundGenerateElements(Layout $subject, callable $proceed)
    {
        $proceed();

        if (!$this->customerSession->isLoggedIn()
            && $this->persistentData->isEnabled()
            && $this->persistentData->isRememberMeEnabled()
        ) {
            if ($subject->getBlock('head.additional') &&
                !$subject->getBlock('persistent_initial_configs')) {
                $subject->addBlock(
                    RememberMeInit::class,
                    'persistent_initial_configs'
                );
                $subject->addOutputElement('persistent_initial_configs');
            }
        }
    }
}
