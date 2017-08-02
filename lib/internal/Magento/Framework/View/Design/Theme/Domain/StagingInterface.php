<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Domain;

/**
 * Interface StagingInterface
 * @since 2.0.0
 */
interface StagingInterface
{
    /**
     * Copy changes from 'staging' theme
     *
     * @return \Magento\Framework\View\Design\Theme\Domain\StagingInterface
     * @since 2.0.0
     */
    public function updateFromStagingTheme();
}
