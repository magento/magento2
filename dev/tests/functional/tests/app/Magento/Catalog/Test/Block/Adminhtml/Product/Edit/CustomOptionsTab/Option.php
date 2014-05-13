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

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\CustomOptionsTab;

use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Block\Block;
use Mtf\Factory\Factory;

/**
 * Select Type
 */
class Option extends Block
{
    /**
     * Create block of special type
     *
     * @param string $type
     * @param Element $element
     * @throws \InvalidArgumentException
     * @return Block|\Magento\Catalog\Test\Block\Adminhtml\Product\Edit\CustomOptionsTab\TypeSelect
     */
    protected function factory($type, Element $element)
    {
        switch ($type) {
            case 'Drop-down':
                return Factory::getBlockFactory()
                    ->getMagentoCatalogAdminhtmlProductEditCustomOptionsTabTypeSelect($element);
                break;
            default:
                throw new \InvalidArgumentException('Option type is not set');
        }
    }

    /**
     * Fill
     *
     * @param array $data
     */
    public function fill($data)
    {
        $this->_rootElement->find('.fieldset-alt [name$="[title]"]')
            ->setValue($data['title']);
        $this->_rootElement->find('.fieldset-alt [name$="[type]"]', Locator::SELECTOR_CSS, 'select')
            ->setValue($data['type']);

        $addButton = $this->_rootElement->find('.add-select-row');
        $table = $this->_rootElement->find('.data-table');
        foreach ($data['options'] as $index => $value) {
            $addButton->click();
            $subRow = $table->find('tbody tr:nth-child(' . ($index + 1) . ')');
            $this->factory($data['type'], $subRow)->fill($value);
        }
    }
}
