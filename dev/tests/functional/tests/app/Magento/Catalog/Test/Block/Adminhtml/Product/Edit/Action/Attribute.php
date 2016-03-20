<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Action;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Product attribute massaction edit page.
 */
class Attribute extends Form
{
    /**
     * Fill the root form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $data = $fixture->getData();
        $fields = [];
        foreach ($data as $name => $dataValue) {
            $fields['toggle_' . $name] = 'Yes';
            $fields[$name] = $dataValue;
        }
        $mapping = $this->dataMapping($fields);
        $this->_fill($mapping, $element);

        return $this;
    }
}
