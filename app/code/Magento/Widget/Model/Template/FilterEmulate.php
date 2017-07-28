<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Template;

/**
 * Class \Magento\Widget\Model\Template\FilterEmulate
 *
 * @since 2.0.0
 */
class FilterEmulate extends Filter
{
    /**
     * Generate widget with emulation frontend area
     *
     * @param string[] $construction
     * @return string
     * @since 2.0.0
     */
    public function widgetDirective($construction)
    {
        return $this->_appState->emulateAreaCode('frontend', [$this, 'generateWidget'], [$construction]);
    }
}
