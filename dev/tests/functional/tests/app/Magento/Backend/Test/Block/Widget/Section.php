<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Widget;

use Magento\Mtf\Block\Form as AbstractForm;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Is used to represent any (collapsible) section on the page.
 */
class Section extends AbstractForm
{
    /**
     * Expand section.
     *
     * @return FormSections
     */
    public function expand()
    {
        return;
    }

    /**
     * Open section.
     *
     * @return FormSections
     */
    public function collapse()
    {
        return;
    }

    /**
     * Check whether section is expanded.
     *
     * @return bool
     */
    public function isExpanded()
    {
        return true;
    }

    /**
     * Get data of the section.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     */
    public function getSectionData($fields = null, SimpleElement $element = null)
    {
        $data = $this->dataMapping($fields);
        return $this->_getData($data, $element);
    }

    /**
     * Fill data into fields in the section.
     *
     * @param array $fields
     * @param SimpleElement|null $contextElement
     * @return $this
     */
    public function fillSection(array $fields, SimpleElement $contextElement = null)
    {
        $data = $this->dataMapping($fields);
        $this->_fill($data, $contextElement);

        return $this;
    }
}
