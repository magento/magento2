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
 * @since 2.0.0
 */
interface StepInterface extends \Magento\Framework\View\Element\BlockInterface
{
    /**
     * Get step caption
     *
     * @return string
     * @since 2.0.0
     */
    public function getCaption();

    /**
     * Get step content
     *
     * @return string
     * @since 2.0.0
     */
    public function getContent();

    /**
     * Get step component name
     *
     * @return string
     * @since 2.0.0
     */
    public function getComponentName();

    /**
     * Get Component Name
     *
     * @return string
     * @since 2.0.0
     */
    public function getParentComponentName();
}
