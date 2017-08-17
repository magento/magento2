<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Template Filter Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Model\Template;

/**
 * Class \Magento\Newsletter\Model\Template\Filter
 *
 */
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
