<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Block\Adminhtml\Catalog\Edit;

use Magento\Backend\Test\Block\Widget\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Element;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Catalog URL rewrite edit form.
 */
class UrlRewriteForm extends Form
{
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
        $data = $fixture->getData();
        if (empty($data['entity_type']) && empty($this->getData()['target_path']) && !isset($data['target_path'])) {
            $entity = $fixture->getDataFieldConfig('target_path')['source']->getEntity();
            $data['target_path'] = $entity->hasData('identifier')
                ? $entity->getIdentifier()
                : $entity->getUrlKey() . '.html';
        }

        foreach ($replace as $key => $value) {
            if (isset($data[$key])) {
                $data[$key] = preg_replace('`(\$.*?' . $value['name'] . '\$)`', $value['value'], $data[$key]);
            }
        }

        $mapping = $this->dataMapping($data);
        $this->_fill($mapping, $element);

        return $this;
    }
}
