<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Template Filter Model
 */
namespace Magento\Newsletter\Model\Template;

class Filter extends \Magento\Widget\Model\Template\FilterEmulate
{
    /**
     * Generate widget HTML if template variables are assigned
     *
     * @param string[] $construction
     * @return string
     */
    public function widgetDirective($construction)
    {
        if (!isset($this->templateVars['subscriber'])) {
            return $construction[0];
        }
        $construction[2] .= sprintf(' store_id ="%s"', $this->getStoreId());
        return parent::widgetDirective($construction);
    }
}
