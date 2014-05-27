<?php
/**
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

namespace Magento\ConfigurableProduct\Test\Block\Backend\Product\Attribute;

use Mtf\Client\Element;
use Magento\Backend\Test\Block\Widget\Form;
use Magento\ConfigurableProduct\Test\Fixture\CatalogProductConfigurable;

/**
 * Class Edit
 * Product attribute edit page
 */
class Edit extends Form
{
    /**
     * Frontend properties selector
     *
     * @var string
     */
    protected $frontendProperties = '#front_fieldset-wrapper .title';

    /**
     * Save attribute selector
     *
     * @var string
     */
    protected $saveAttribute = '[data-ui-id="attribute-edit-content-save-button"]';

    /**
     * 'Add new option' button selector
     *
     * @var string
     */
    protected $addNewOption = '#add_new_option_button';

    /**
     * Attribute option row
     *
     * @var string
     */
    protected $optionRow = '//*[@id="manage-options-panel"]//tbody//tr[%row%]';

    /**
     * Open frontend properties
     *
     * @return void
     */
    public function openFrontendProperties()
    {
        $this->_rootElement->find($this->frontendProperties)->click();
    }

    /**
     * Save attribute
     *
     * @return void
     */
    public function saveAttribute()
    {
        $this->_rootElement->find($this->saveAttribute)->click();
    }

    /**
     * Fill attribute options
     *
     * @param array $data
     * @return void
     */
    public function fillAttributeOption(array $data)
    {
        $this->_rootElement->find('#attribute_label')
            ->setValue($data['attribute_options']['attribute_label']);
        $this->_rootElement->find('#frontend_input', Element\Locator::SELECTOR_CSS, 'select')
            ->setValue($data['attribute_options']['frontend_input']);
        $this->_rootElement->find('#is_required', Element\Locator::SELECTOR_CSS, 'select')
            ->setValue($data['attribute_options']['is_required']);

        $addButton = $this->_rootElement->find('#add_new_option_button');
        $table = $this->_rootElement->find('.data-table');
        foreach ($data['attribute_options']['options'] as $index => $value) {
            $addButton->click();
            $table->find('[name="' . $index . '"]')->setValue($value);
        }
        $this->saveAttribute();
    }
}
