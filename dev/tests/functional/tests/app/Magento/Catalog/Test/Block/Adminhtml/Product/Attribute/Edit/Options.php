<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
    protected $addOption = 'button[data-action="add_new_row"]';

    /**
     * Option form selector.
     *
     * @var string
     */
    protected $option = '[data-index="attribute_options_select"] tbody tr';

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
            \Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit\Tab\Options\Option::class,
            ['element' => $element === null ? $this->find($this->option . ':last-child') : $element]
        );
    }
}
