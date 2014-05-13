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

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit;

use Mtf\Client\Element;
use Magento\Backend\Test\Block\Widget\Tab;
use Mtf\Factory\Factory;

/**
 * Custom Options Tab
 *
 */
class CustomOptionsTab extends Tab
{
    /**
     * Fill custom options
     *
     * @param array $fields
     * @param Element $element
     * @return $this
     */
    public function fillFormTab(array $fields, Element $element)
    {
        if (!isset($fields['custom_options'])) {
            return $this;
        }
        $root = $element;
        $this->_rootElement->waitUntil(
            function () use ($root) {
                return $root->find('#Custom_Options')->isVisible();
            }
        );

        $button = $root->find('[data-ui-id="admin-product-options-add-button"]');

        $container = $root->find('#product_options_container');

        if (isset($fields['custom_options']['value'])) {
            foreach ($fields['custom_options']['value'] as $index => $data) {
                $button->click();
                $row = $container->find('.fieldset-wrapper:nth-child(' . ($index + 1) . ')');
                Factory::getBlockFactory()
                    ->getMagentoCatalogAdminhtmlProductEditCustomOptionsTabOption($row)
                    ->fill($data);
            }
        }

        return $this;
    }
}
