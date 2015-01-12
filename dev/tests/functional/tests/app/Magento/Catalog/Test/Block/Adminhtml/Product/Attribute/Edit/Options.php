<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit;

use Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit\Tab\Options\Option;
use Mtf\Client\Driver\Selenium\Element;
use Mtf\ObjectManager;

/**
 * Options element.
 */
class Options extends Element
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
        $options = $this->find($this->option)->getElements();
        foreach ($options as $option) {
            $data[] = $this->getFormInstance($option)->getData();
        }
        return $data;
    }

    /**
     * Get options form.
     *
     * @param Element|null $element
     * @return Option
     */
    protected function getFormInstance(Element $element = null)
    {
        return ObjectManager::getInstance()->create(
            'Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit\Tab\Options\Option',
            ['element' => $element === null ? $this->find($this->option . ':last-child') : $element]
        );
    }
}
