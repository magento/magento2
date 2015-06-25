<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Block\Adminhtml\Catalog\Edit;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Element;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Catalog URL rewrite edit form.
 */
class UrlRewriteForm extends Form
{
    /**
     * Prepare data for url rewrite fixture.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture)
    {
        $data = $fixture->getData();
        if (empty($data['entity_type']) && empty($this->getData()['target_path']) && !isset($data['target_path'])) {
            $entity = $fixture->getDataFieldConfig('target_path')['source']->getEntity();
            $data['target_path'] = $entity->hasData('identifier')
                ? $entity->getIdentifier()
                : $entity->getUrlKey() . '.html';
        }
        return $data;
    }

    /**
     * Fill visible fields on the form.
     *
     * @param array $data
     * @param SimpleElement $context
     * @retun void
     */
    protected function fillFields(array $data, SimpleElement $context)
    {
        $mapping = $this->dataMapping($data);
        foreach ($mapping as $field) {
            $element = $this->getElement($context, $field);
            if ($element->isVisible() && !$element->isDisabled()) {
                $element->setValue($field['value']);
            }
        }
    }

    /**
     * Fill the root form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @param array $replace [optional]
     * @return $this
     */
    public function fill(
        FixtureInterface $fixture,
        SimpleElement $element = null,
        array $replace = []
    ) {
        $context = ($element === null) ? $this->_rootElement : $element;
        $data = $this->prepareData($fixture);

        foreach ($replace as $key => $value) {
            if (isset($data[$key])) {
                $data[$key] = preg_replace('`(\$.*?' . $value['name'] . '\$)`', $value['value'], $data[$key]);
            }
        }

        $this->fillFields($data, $context);

        return $this;
    }
}
