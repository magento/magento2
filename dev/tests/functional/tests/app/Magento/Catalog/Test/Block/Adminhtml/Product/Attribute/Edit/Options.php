<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit\Tab\Options\Option;

/**
 * Options element.
 */
class Options extends SimpleElement
{
    /**
     * 'Add Option' button.
     *
     * @var string
     */
    protected $addOption = '#add_new_option_button';

    /**
     * Option form selector.
     *
     * @var string
     */
    protected $option = '.ui-sortable tr';

    /**
     * Set value.
     *
     * @param array $preset
     */
    public function setValue($preset)
    {
        foreach ($preset as $option) {
            if (isset($option['admin'])) {
                $this->find($this->addOption)->click();
                $this->getFormInstance()->fillOptions($option);
            }
        }
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        $data = [];
        $options = $this->getElements($this->option);
        foreach ($options as $option) {
            $data[] = $this->getFormInstance($option)->getData();
        }
        return $data;
    }

    /**
     * Get options form.
     *
     * @param SimpleElement|null $element
     * @return Option
     */
    protected function getFormInstance(SimpleElement $element = null)
    {
        return ObjectManager::getInstance()->create(
            'Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit\Tab\Options\Option',
            ['element' => $element === null ? $this->find($this->option . ':last-child') : $element]
        );
    }
}
