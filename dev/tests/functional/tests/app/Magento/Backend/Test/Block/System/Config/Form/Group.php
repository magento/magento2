<?php
/**
 * Store configuration group.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\System\Config\Form;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Block\Form;

/**
 * Class Group.
 */
class Group extends Form
{
    /**
     * Fieldset selector.
     *
     * @var string
     */
    protected $fieldset = '#%s_%s';

    /**
     * Field selector.
     *
     * @var string
     */
    protected $field = '#%s_%s_%s';

    /**
     * Default checkbox selector.
     *
     * @var string
     */
    protected $defaultCheckbox = '#%s_%s_%s_inherit';

    /**
     * Set store configuration value by element data-ui-id.
     *
     * @param string $tabName
     * @param string $groupName
     * @param string $fieldName
     * @param mixed $value
     */
    public function setValue($tabName, $groupName, $fieldName, $value)
    {
        $input = null;
        $attribute = $this->_rootElement->find(
            sprintf($this->field, $tabName, $groupName, $fieldName),
            Locator::SELECTOR_CSS
        )->getAttribute('data-ui-id');

        $parts = explode('-', $attribute, 2);
        if (in_array($parts[0], ['select', 'text', 'checkbox'])) {
            $input = $parts[0];
        }

        $element = $this->_rootElement->find(
            sprintf($this->field, $tabName, $groupName, $fieldName),
            Locator::SELECTOR_CSS,
            $input
        );

        if ($element->isDisabled()) {
            $checkbox = $this->_rootElement->find(
                sprintf($this->defaultCheckbox, $tabName, $groupName, $fieldName),
                Locator::SELECTOR_CSS,
                'checkbox'
            );
            $checkbox->setValue('No');
        }

        $element->setValue($value);
    }

    /**
     * Set store configuration value by element data-ui-id.
     *
     * @param string $tabName
     * @param string $groupName
     * @param string $fieldName
     * @return array/string
     */
    public function getValue($tabName, $groupName, $fieldName)
    {
        $input = null;
        $attribute = $this->_rootElement->find(
            sprintf($this->field, $tabName, $groupName, $fieldName),
            Locator::SELECTOR_CSS
        )->getAttribute('data-ui-id');

        $parts = explode('-', $attribute, 2);
        if (in_array($parts[0], ['select', 'text', 'checkbox'])) {
            $input = $parts[0];
        }

        $element = $this->_rootElement->find(
            sprintf($this->field, $tabName, $groupName, $fieldName),
            Locator::SELECTOR_CSS,
            $input
        );

        if ($element->isDisabled()) {
            $checkbox = $this->_rootElement->find(
                sprintf($this->defaultCheckbox, $tabName, $groupName, $fieldName),
                Locator::SELECTOR_CSS,
                'checkbox'
            );
            $checkbox->setValue('No');
        }

        return $element->getValue();
    }

    /**
     * Check if a field is visible in a given group.
     *
     * @param string $tabName
     * @param string $groupName
     * @param string $fieldName
     * @return bool
     */
    public function isFieldVisible($tabName, $groupName, $fieldName)
    {
        return $this->_rootElement->find(
            sprintf($this->field, $tabName, $groupName, $fieldName),
            Locator::SELECTOR_CSS
        )->isVisible();
    }

    /**
     * Check if a field is disabled in a given group.
     *
     * @param string $tabName
     * @param string $groupName
     * @param string $fieldName
     * @return bool
     */
    public function isFieldDisabled($tabName, $groupName, $fieldName)
    {
        return $this->_rootElement->find(
            sprintf($this->field, $tabName, $groupName, $fieldName),
            Locator::SELECTOR_CSS
        )->isDisabled();
    }
}
