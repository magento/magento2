<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Block\Adminhtml;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Is used to represent an abstract container of fields on the page.
 */
abstract class AbstractContainer extends Form
{
    /**
     * Get data of the container.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     */
    public function getFieldsData($fields = null, SimpleElement $element = null)
    {
        $data = $this->dataMapping($fields);
        return $this->_getData($data, $element);
    }

    /**
     * Fill data into fields in the container.
     *
     * @param array $fields
     * @param SimpleElement|null $contextElement
     * @return $this
     */
    public function setFieldsData(array $fields, SimpleElement $contextElement = null)
    {
        $data = $this->dataMapping($fields);
        $this->_fill($data, $contextElement);

        return $this;
    }
}
