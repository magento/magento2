<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Template Filter Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Model\Template;

class Filter extends \Magento\Widget\Model\Template\Filter
{
    /**
     * Generate widget HTML if template variables are assigned
     *
     * @param string[] $construction
     * @return string
     */
    public function widgetDirective($construction)
    {
        if (!isset($this->_templateVars['subscriber'])) {
            return $construction[0];
        }
        $construction[2] .= sprintf(' store_id ="%s"', $this->getStoreId());
        return parent::widgetDirective($construction);
    }
}
