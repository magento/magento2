<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Block\Adminhtml;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Is used to represent any (collapsible) section on the page.
 */
class Section extends Form
{
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