<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Block\Component\StepsWizard;

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
