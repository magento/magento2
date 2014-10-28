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

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Action;

use Mtf\Fixture;
use Mtf\Client\Element;
use Magento\Backend\Test\Block\Widget\Form;

/**
 * Class Attribute
 * Product attribute massaction edit page
 */
class Attribute extends Form
{
    /**
     * CSS selector for 'save' button
     *
     * @var string
     */
    protected $saveButton = '[data-ui-id="attribute-save-button"]';

    /**
     * XPath selector for checkbox that enables price editing
     *
     * @var string
     */
    protected $priceFieldEnablerSelector = '//*[@id="attribute-price-container"]/div[1]/div/label//*[@type="checkbox"]';

    /**
     * Enable price field editing
     *
     * @return void
     */
    public function enablePriceEdit()
    {
        $this->_rootElement->find(
            $this->priceFieldEnablerSelector,
            Element\Locator::SELECTOR_XPATH,
            'checkbox'
        )->setValue('Yes');
    }
}
