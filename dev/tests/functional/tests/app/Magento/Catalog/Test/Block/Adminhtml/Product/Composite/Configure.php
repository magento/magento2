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
 * @spi
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Composite;

use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;
use Magento\Backend\Test\Block\Template;
use Magento\Catalog\Test\Block\AbstractConfigureBlock;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Class Configure
 * Adminhtml catalog product composite configure block
 */
class Configure extends AbstractConfigureBlock
{
    /**
     * Custom options CSS selector
     *
     * @var string
     */
    protected $customOptionsSelector = '#product_composite_configure_fields_options';

    /**
     * Selector for "Ok" button
     *
     * @var string
     */
    protected $okButton = '.ui-dialog-buttonset button:nth-of-type(2)';

    /**
     * Backend abstract block
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * Set quantity
     *
     * @param int $qty
     * @return void
     */
    public function setQty($qty)
    {
        $this->_fill($this->dataMapping(['qty' => $qty]));
    }

    /**
     * Fill in the option specified for the product
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function configProduct(FixtureInterface $product)
    {
        $checkoutData = null;
        if ($product instanceof InjectableFixture) {
            /** @var CatalogProductSimple $product */
            $checkoutData = $product->getCheckoutData();
        }

        $this->fillOptions($product);
        if (isset($checkoutData['qty'])) {
            $this->setQty($checkoutData['qty']);
        }
        $this->clickOk();
    }

    /**
     * Click "Ok" button
     *
     * @return void
     */
    public function clickOk()
    {
        $this->_rootElement->find($this->okButton)->click();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Get backend abstract block
     *
     * @return \Magento\Backend\Test\Block\Template
     */
    public function getTemplateBlock()
    {
        return $this->blockFactory->create(
            'Magento\Backend\Test\Block\Template',
            ['element' => $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
