<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Select
 */
class Select extends AbstractElement
{
    const NAME = 'select';

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');
        $options = $this->getData('options');

        if ($options !== null) {
            if (!isset($config['options'])) {
                $config['options'] = [];
            }
            if ($options instanceof OptionSourceInterface) {
                $optionsData = $options->toOptionArray();
            } else {
                $optionsData = array_values($options);
            }
            $config['options'] = array_values(array_merge_recursive($optionsData, $config['options']));
        }
        $this->setData('config', (array)$config);
        parent::prepare();
    }

    /**
     * Check if option value
     *
     * @param string $optionValue
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsSelected($optionValue)
    {
        return $this->getValue() == $optionValue;
    }
}
