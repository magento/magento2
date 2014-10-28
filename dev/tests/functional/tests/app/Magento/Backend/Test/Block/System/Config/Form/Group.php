<?php
/**
 * Store configuration group
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Test\Block\System\Config\Form;

use \Magento\Backend\Test\Block\Widget\Form;
use Mtf\Client\Element;

class Group extends Form
{
    /**
     * Fieldset selector
     *
     * @var string
     */
    protected $fieldset = 'fieldset';

    /**
     * Toggle link
     *
     * @var string
     */
    protected $toogleLink = '.entry-edit-head a';

    /**
     * Field element selector
     *
     * @var string
     */
    protected $element = '//*[@data-ui-id="%s"]';

    /**
     * Default checkbox selector
     *
     * @var string
     */
    protected $defaultCheckbox = '//*[@data-ui-id="%s"]/../../*[@class="use-default"]/input';

    /**
     * Open group fieldset
     */
    public function open()
    {
        if (!$this->_rootElement->find($this->fieldset)->isVisible()) {
            $this->_rootElement->find($this->toogleLink)->click();
        }
    }

    /**
     * Set store configuration value by element data-ui-id
     *
     * @param string $field
     * @param mixed $value
     */
    public function setValue($field, $value)
    {
        $input = null;
        $fieldParts = explode('-', $field);
        if (in_array($fieldParts[0], array('select', 'checkbox'))) {
            $input = $fieldParts[0];
        }

        $element = $this->_rootElement->find(
            sprintf($this->element, $field),
            Element\Locator::SELECTOR_XPATH,
            $input
        );

        if ($element->isDisabled()) {
            $checkbox = $this->_rootElement->find(
                sprintf($this->defaultCheckbox, $field),
                Element\Locator::SELECTOR_XPATH,
                'checkbox'
            );
            $checkbox->setValue('No');
        }

        $element->setValue($value);
    }
}
