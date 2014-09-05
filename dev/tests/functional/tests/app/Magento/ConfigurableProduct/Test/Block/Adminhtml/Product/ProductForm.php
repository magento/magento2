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

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product;

use Mtf\Block\Mapper;
use Mtf\Client\Element;
use Mtf\Client\Browser;
use Mtf\Factory\Factory;
use Mtf\Fixture\FixtureInterface;
use Mtf\Util\XmlConverter;
use Mtf\Block\BlockFactory;
use Mtf\Client\Element\Locator;
use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Block\Adminhtml\Product\ProductForm as ParentForm;

/**
 * Class ProductForm
 * Product creation form
 */
class ProductForm extends ParentForm
{
    /**
     * New attribute selector
     *
     * @var string
     */
    protected $newAttribute = 'body';

    /**
     * New attribute frame selector
     *
     * @var string
     */
    protected $newAttributeFrame = '#create_new_attribute_container';

    /**
     * New variation set button selector
     *
     * @var string
     */
    protected $newVariationSet = '[data-ui-id="admin-product-edit-tab-super-config-grid-container-add-attribute"]';

    /**
     * Variations tab selector
     *
     * @var string
     */
    protected $productDetailsTab = '#product_info_tabs_product-details';

    /**
     * Choose affected attribute set dialog popup window
     *
     * @var string
     */
    protected $affectedAttributeSet = "//div[div/@data-id='affected-attribute-set-selector']";

    /**
     * Variations tab selector
     *
     * @var string
     */
    protected $variationsTab = '#product_info_tabs_super_config_content .title';

    /**
     * Variations wrapper selector
     *
     * @var string
     */
    protected $variationsWrapper = '#product_info_tabs_super_config_content';

    /**
     * Get choose affected attribute set dialog popup window
     *
     * @return \Magento\ConfigurableProduct\Test\Block\Backend\Product\AffectedAttributeSet
     */
    protected function getAffectedAttributeSetBlock()
    {
        return Factory::getBlockFactory()->getMagentoConfigurableProductBackendProductAffectedAttributeSet(
            $this->_rootElement->find($this->affectedAttributeSet, Locator::SELECTOR_XPATH)
        );
    }

    /**
     * Get attribute edit block
     *
     * @return \Magento\ConfigurableProduct\Test\Block\Backend\Product\Attribute\Edit
     */
    public function getConfigurableAttributeEditBlock()
    {
        $this->browser->switchToFrame(new Locator($this->newAttributeFrame));
        return Factory::getBlockFactory()->getMagentoConfigurableProductBackendProductAttributeEdit(
            $this->browser->find($this->newAttribute, Locator::SELECTOR_TAG_NAME)
        );
    }

    /**
     * Save product
     *
     * @param FixtureInterface $fixture
     * @return \Magento\Backend\Test\Block\Widget\Form|void
     */
    public function save(FixtureInterface $fixture = null)
    {
        parent::save($fixture);
        if ($this->getAffectedAttributeSetBlock()->isVisible()) {
            $this->getAffectedAttributeSetBlock()->chooseAttributeSet($fixture);
        }
    }

    /**
     * Get variations block
     *
     * @return \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config
     */
    protected function getVariationsBlock()
    {
        return Factory::getBlockFactory()->getMagentoConfigurableProductAdminhtmlProductEditTabSuperConfig(
            $this->browser->find($this->variationsWrapper)
        );
    }

    /**
     * Fill product variations
     *
     * @param array $variations
     * @return void
     */
    public function variationsFill(array $variations)
    {
        $variationsBlock = $this->getVariationsBlock();
        $variationsBlock->fillAttributeOptions($variations);
        $variationsBlock->generateVariations();
    }

    /**
     * Open variations tab
     *
     * @return void
     */
    public function openVariationsTab()
    {
        $this->_rootElement->find($this->variationsTab)->click();
    }

    /**
     * Click on 'Create New Variation Set' button
     *
     * @return void
     */
    public function clickCreateNewVariationSet()
    {
        $this->_rootElement->find($this->newVariationSet)->click();
    }

    /**
     * Find Attribute on Product page
     *
     * @param string $attributeName
     * @return bool
     */
    public function findAttribute($attributeName)
    {
        $this->openTab('product-details');

        return $this->getVariationsBlock()->getAttributeBlock($attributeName)->isVisible();
    }
}
