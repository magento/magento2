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

namespace Magento\Catalog\Test\Block\Product\View;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Factory\Factory;

/**
 * Class Custom Options
 *
 */
class CustomOptions extends Block
{
    protected $fieldsetSelector = '.fieldset';
    protected $rowSelector = '.field';

    /**
     * Get options
     *
     * @return array
     */
    public function get()
    {
        $optionsFieldset = $this->_rootElement->find($this->fieldsetSelector);
        $fieldsetIndex = 1;
        $options = array();
        //@todo move to separate block
        $field = $optionsFieldset->find($this->rowSelector . ':nth-of-type(' . $fieldsetIndex . ')');
        while ($field->isVisible()) {
            $optionFieldset = [];
            $optionFieldset['title'] = $field->find('.label')->getText();
            $optionFieldset['is_require'] = $field->find('select.required')->isVisible();
            $options[] = & $optionFieldset;
            $optionIndex = 1;
            //@todo move to separate block
            $option = $field->find('select > option:nth-of-type(' . $optionIndex . ')');
            while ($option->isVisible()) {
                if (preg_match('~^(?<title>.+) .?\$(?P<price>\d+\.\d*)$~', $option->getText(), $matches) !== false
                    && !empty($matches['price'])
                ) {
                    $optionFieldset['options'][] = [
                        'title' => $matches['title'],
                        'price' => $matches['price'],
                    ];
                };
                $optionIndex++;
                $option = $field->find('select > option:nth-of-type(' . $optionIndex . ')');
            }
            $fieldsetIndex++;
            $field = $optionsFieldset->find($this->rowSelector . ':nth-of-type(' . $fieldsetIndex . ')');
        }
        return $options;
    }

}
