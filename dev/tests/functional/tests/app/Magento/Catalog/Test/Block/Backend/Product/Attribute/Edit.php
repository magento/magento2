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

namespace Magento\Catalog\Test\Block\Backend\Product\Attribute;

use Mtf\Fixture\FixtureInterface;
use Mtf\Client\Element;
use Magento\Backend\Test\Block\Widget\Form;

/**
 * Product attribute edit page
 *
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
     */
    public function openFrontendProperties()
    {
        $this->_rootElement->find($this->frontendProperties)->click();
    }

    /**
     * Save attribute
     */
    public function saveAttribute()
    {
        $this->_rootElement->find($this->saveAttribute)->click();
    }

    /**
     * Fill form with attribute options
     *
     * @param FixtureInterface $fixture
     * @param Element|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, Element $element = null)
    {
        parent::fill($fixture, $element);
        $this->fillOptions($fixture);

        return $this;
    }

    /**
     * Fill attribute options
     *
     * @param FixtureInterface $fixture
     */
    protected function fillOptions(FixtureInterface $fixture)
    {
        /** @var $fixture \Magento\Catalog\Test\Fixture\ProductAttribute */
        $options = $fixture->getOptions();

        $row = 1;
        foreach ($options as $option) {
            $this->_rootElement->find($this->addNewOption)->click();
            // TODO: implement filling for any number of stores
            $this->_rootElement->find(
                str_replace('%row%', $row, $this->optionRow) . '/td[2]/input',
                Element\Locator::SELECTOR_XPATH,
                'checkbox'
            )->setValue($option['default']['value']);
            $this->_rootElement->find(
                str_replace('%row%', $row, $this->optionRow) . '/td[3]/input',
                Element\Locator::SELECTOR_XPATH
            )->setValue($option['label']['value']);
            ++$row;
        }
    }
}
