<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * @api
 * @since 100.1.0
 */
abstract class AbstractOptionsField extends AbstractElement
{
    /**
     * @var array|OptionSourceInterface|null
     * @since 100.1.0
     */
    protected $options;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param array|OptionSourceInterface|null $options
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        $options = null,
        array $components = [],
        array $data = []
    ) {
        $this->options = $options;
        parent::__construct($context, $components, $data);
    }

    /**
     * Prepare component configuration
     *
     * @return void
     * @since 100.1.0
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (isset($this->options)) {
            if (!isset($config['options'])) {
                $config['options'] = [];
            }
            if ($this->options instanceof OptionSourceInterface) {
                $options = $this->options->toOptionArray();
            } else {
                $options = array_values($this->options);
            }
            if (empty($config['rawOptions'])) {
                $options = $this->convertOptionsValueToString($options);
            }
            $config['options'] = array_values(array_merge_recursive($config['options'], $options));
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
     * @since 100.1.0
     */
    abstract public function getIsSelected($optionValue);

    /**
     * Convert options value to string
     *
     * @param array $options
     * @return array
     * @since 100.1.0
     */
    protected function convertOptionsValueToString(array $options)
    {
        array_walk($options, function (&$value) {
            if (isset($value['value']) && is_scalar($value['value'])) {
                $value['value'] = (string)$value['value'];
            }
        });
        return $options;
    }
}
