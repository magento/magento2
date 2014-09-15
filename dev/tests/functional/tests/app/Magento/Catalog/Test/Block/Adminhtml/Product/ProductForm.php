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

namespace Magento\Catalog\Test\Block\Adminhtml\Product;

use Mtf\Client\Element;
use Mtf\Fixture\DataFixture;
use Mtf\Client\Element\Locator;
use Magento\Backend\Test\Block\Widget\FormTabs;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;

/**
 * Class ProductForm
 * Product form on backend product page
 */
class ProductForm extends FormTabs
{
    /**
     * Attribute on the Product page
     *
     * @var string
     */
    protected $attribute = './/*[contains(@class,"label")]/span[text()="%s"]';

    /**
     * Attribute Search locator the Product page
     *
     * @var string
     */
    protected $attributeSearch = '#product-attribute-search-container';

    /**
     * Selector for trigger(show/hide) of advanced setting content
     *
     * @var string
     */
    protected $advancedSettingTrigger = '#product_info_tabs-advanced [data-role="trigger"]';

    /**
     * Selector for advanced setting content
     *
     * @var string
     */

    protected $advancedSettingContent = '#product_info_tabs-advanced [data-role="content"]';

    /**
     * Custom Tab locator
     *
     * @var string
     */
    protected $customTab = './/*/a[contains(@id,"product_info_tabs_%s")]';

    /**
     * Button "New Category"
     *
     * @var string
     */
    protected $buttonNewCategory = '#add_category_button';

    /**
     * Dialog box "Create Category"
     *
     * @var string
     */
    protected $createCategoryDialog = './/ancestor::body//*[contains(@class,"mage-new-category-dialog")]';

    /**
     * "Parent Category" block on dialog box
     *
     * @var string
     */
    protected $parentCategoryBlock = '//*[contains(@class,"field-new_category_parent")]';

    /**
     * Field "Category Name" on dialog box
     *
     * @var string
     */
    protected $fieldNewCategoryName = '//input[@id="new_category_name"]';

    /**
     * Button "Create Category" on dialog box
     *
     * @var string
     */
    protected $createCategoryButton = '//button[contains(@class,"action-create")]';

    /**
     * Fill the product form
     *
     * @param FixtureInterface $product
     * @param Element|null $element [optional]
     * @param FixtureInterface|null $category [optional]
     * @return FormTabs
     */
    public function fill(FixtureInterface $product, Element $element = null, FixtureInterface $category = null)
    {
        $dataConfig = $product->getDataConfig();
        $typeId = isset($dataConfig['type_id']) ? $dataConfig['type_id'] : null;

        if ($this->hasRender($typeId)) {
            $renderArguments = [
                'product' => $product,
                'element' => $element,
                'category' => $category
            ];
            $this->callRender($typeId, 'fill', $renderArguments);
        } else {
            $tabs = $this->getFieldsByTabs($product);

            if (null === $category && $product instanceof DataFixture) {
                $categories = $product->getCategories();
                $category = reset($categories);
            }
            if ($category) {
                $tabs['product-details']['category_ids']['value'] = ($category instanceof InjectableFixture )
                    ? $category->getName()
                    : $category->getCategoryName();
            }

            $this->showAdvancedSettings();
            $this->fillTabs($tabs, $element);
        }

        return $this;
    }

    /**
     * Get data of the tabs
     *
     * @param FixtureInterface|null $fixture
     * @param Element|null $element
     * @return array
     */
    public function getData(FixtureInterface $fixture = null, Element $element = null)
    {
        $this->showAdvancedSettings();
        return parent::getData($fixture, $element);
    }

    /**
     * Show Advanced Setting
     *
     * @return void
     */
    protected function showAdvancedSettings()
    {
        if (!$this->_rootElement->find($this->advancedSettingContent)->isVisible()) {
            $this->_rootElement->find($this->advancedSettingTrigger)->click();
            $this->waitForElementVisible($this->advancedSettingContent);
        }
    }

    /**
     * Open tab
     *
     * @param string $tabName
     * @return Tab
     */
    public function openTab($tabName)
    {
        $this->showAdvancedSettings();

        return parent::openTab($tabName);
    }

    /**
     * Save new category
     *
     * @param Product $fixture
     * @return void
     */
    public function addNewCategory(Product $fixture)
    {
        $this->openTab('product-details');
        $this->openNewCategoryDialog();
        $this->_rootElement->find(
            $this->createCategoryDialog . $this->fieldNewCategoryName,
            Locator::SELECTOR_XPATH
        )->setValue($fixture->getNewCategoryName());

        $this->clearCategorySelect();
        $this->selectParentCategory();

        $buttonCreateCategory = $this->createCategoryDialog . $this->createCategoryButton;
        $this->_rootElement->find($buttonCreateCategory, Locator::SELECTOR_XPATH)->click();
        $this->waitForElementNotVisible($buttonCreateCategory, Locator::SELECTOR_XPATH);
    }

    /**
     * Select parent category for new one
     *
     * @return void
     */
    protected function selectParentCategory()
    {
        $this->_rootElement->find(
            $this->createCategoryDialog . $this->parentCategoryBlock,
            Locator::SELECTOR_XPATH,
            '\Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\ProductDetails\ParentCategoryIds'
        )->setValue('Default Category');
    }

    /**
     * Clear category field
     *
     * @return void
     */
    public function clearCategorySelect()
    {
        $selectedCategory = 'li.mage-suggest-choice span.mage-suggest-choice-close';
        if ($this->_rootElement->find($selectedCategory)->isVisible()) {
            $this->_rootElement->find($selectedCategory)->click();
        }
    }

    /**
     * Open new category dialog
     *
     * @return void
     */
    protected function openNewCategoryDialog()
    {
        $this->_rootElement->find($this->buttonNewCategory)->click();
        $this->waitForElementVisible($this->createCategoryDialog, Locator::SELECTOR_XPATH);
    }

    /**
     * Check visibility of the attribute on the product page
     *
     * @param mixed $productAttribute
     * @return bool
     */
    public function checkAttributeLabel($productAttribute)
    {
        $frontendLabel = (is_array($productAttribute))
            ? $productAttribute['frontend_label']
            : $productAttribute->getFrontendLabel();
        $attributeLabelLocator = sprintf($this->attribute, $frontendLabel);

        return $this->_rootElement->find($attributeLabelLocator, Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Call method that checking present attribute in search result
     *
     * @param CatalogProductAttribute $productAttribute
     * @return bool
     */
    public function checkAttributeInSearchAttributeForm(CatalogProductAttribute $productAttribute)
    {
        return $this->_rootElement->find(
            $this->attributeSearch,
            Locator::SELECTOR_CSS,
            'Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Attributes\Search'
        )->isExistAttributeInSearchResult($productAttribute);
    }

    /**
     * Check tab visibility on Product form
     *
     * @param string $tabName
     * @return bool
     */
    public function isTabVisible($tabName)
    {
        $tabName = strtolower($tabName);
        $selector = sprintf($this->customTab, $tabName);
        $this->waitForElementVisible($selector, Locator::SELECTOR_XPATH);
        return $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Open custom tab on Product form
     *
     * @param string $tabName
     * @return void
     */
    public function openCustomTab($tabName)
    {
        $tabName = strtolower($tabName);
        $this->_rootElement->find(sprintf($this->customTab, $tabName), Locator::SELECTOR_XPATH)->click();
    }
}
