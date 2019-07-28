<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Block\Component\StepsWizard;

/**
 * Interface for multi-step wizard blocks
 *
 * @api
 * @since 100.0.2
 */
interface StepInterface extends \Magento\Framework\View\Element\BlockInterface
{
    /**
     * Get step caption
     *
     * @return string
     */
    public function getCaption();

    /**
     * Get step content
     *
     * @return string
     */
    public function getContent();

    /**
     * Get step component name
     *
     * @return string
     */
    public function getComponentName();

    /**
     * Get Component Name
     *
     * @return string
     */
    public function getParentComponentName();
}
