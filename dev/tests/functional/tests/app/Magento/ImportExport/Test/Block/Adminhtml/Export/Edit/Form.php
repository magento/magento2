<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Block\Adminhtml\Export\Edit;

use Magento\Mtf\Block\Form as AbstractForm;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class Form
 * Export form
 */
class Form extends AbstractForm
{
    /**
     * Attribute fields.
     *
     * @var array
     */
    private $attributeFields = [];

    /**
     * Form filling.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return void
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $data = $fixture->getData();
        $fields = isset($data['fields']) ? $data['fields'] : $data;
        if ($this->attributeFields) {
            foreach ($this->attributeFields as $field) {
                $fields['product'] = [$field => $fixture->getDataExport()[$field]];
            }
        }
        unset($fields['data_export']);
        $mapping = $this->dataMapping($fields);
        parent::_fill($mapping, $element);
    }

    /**
     * Prepare attribute fields.
     *
     * @param string $attributes
     * @return void
     */
    public function prepareAttributeFields($attributes)
    {
        $this->attributeFields = explode(',', $attributes);
    }
}
