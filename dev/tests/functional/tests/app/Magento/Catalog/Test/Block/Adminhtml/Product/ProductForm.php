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
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Catalog\Test\Fixture\ConfigurableProduct;
use Magento\Catalog\Test\Fixture\CatalogCategory;

/**
 * Class ProductForm
 * Product form on backend product page
 */
class ProductForm extends FormTabs
{
    /**
     * New variation set button selector
     *
     * @var string
     */
    protected $newVariationSet = '[data-ui-id="admin-product-edit-tab-super-config-grid-container-add-attribute"]';

    /**
     * Category name selector
     *
     * @var string
     */
    protected $categoryName = '//*[contains(@class, "mage-suggest-choice")]/*[text()="%categoryName%"]';

    /**
     * 'Advanced Settings' tab
     *
     * @var string
     */
    protected $advancedSettings = '#product_info_tabs-advanced [data-role="trigger"]';

    /**
     * Advanced tab list
     *
     * @var string
     */
    protected $advancedTabList = '#product_info_tabs-advanced[role="tablist"]';

    /**
     * Advanced tab panel
     *
     * @var string
     */
    protected $advancedTabPanel = './/*[role="tablist"]//ul[!contains(@style,"overflow")]';

    /**
     * CSS locator button status of the product
     *
     * @var string
     */
    protected $onlineSwitcher = '#product-online-switcher%s + [for="product-online-switcher"]';

    /**
     * Category fixture
     *
     * @var CatalogCategory
     */
    protected $category;

    /**
     * Fill the product form
     *
     * @param FixtureInterface $fixture
     * @param CatalogCategory $category
     * @param Element $element
     * @return $this
     */
    public function fillProduct(
        FixtureInterface $fixture,
        CatalogCategory $category = null,
        Element $element = null
    ) {
        $this->category = $category;
        $this->fillCategory($fixture);

        if ($fixture instanceof InjectableFixture) {
            $status = $fixture->getStatus();
            if (($status === 'Product offline'
                && $this->_rootElement->find(sprintf($this->onlineSwitcher, ':checked'))->isVisible())
                || ($status === 'Product online'
                && $this->_rootElement->find(sprintf($this->onlineSwitcher, ':not(:checked)'))->isVisible())
            ) {
                $this->_rootElement->find(sprintf($this->onlineSwitcher, ''))->click();
            }
        }

        return parent::fill($fixture, $element);
    }

    /**
     * Fill product variations
     *
     * @param ConfigurableProduct $variations
     * @return void
     */
    public function fillVariations(ConfigurableProduct $variations)
    {
        $variationsBlock = Factory::getBlockFactory()->getMagentoCatalogAdminhtmlProductEditTabSuperConfig(
            $this->_rootElement->find($this->variationsWrapper)
        );
        $variationsBlock->fillAttributeOptions($variations->getConfigurableAttributes());
        $variationsBlock->generateVariations();
        $variationsBlock->fillVariationsMatrix($variations->getVariationsMatrix());
    }

    /**
     * Select category
     *
     * @param FixtureInterface $fixture
     * @return void|null
     */
    protected function fillCategory(FixtureInterface $fixture)
    {
        // TODO should be removed after suggest widget implementation as typified element
        $categoryName = null;
        if (!empty($this->category)) {
            $categoryName = $this->category->getName();
        }
        if (empty($categoryName) && !($fixture instanceof InjectableFixture)) {
            $categoryName = $fixture->getCategoryName();
        }
        if (empty($categoryName)) {
            return;
        }

        $category = $this->_rootElement->find(
            str_replace(
                '%categoryName%',
                $categoryName,
                $this->categoryName
            ),
            Locator::SELECTOR_XPATH
        );
        if (!$category->isVisible()) {
            $this->fillCategoryField(
                $categoryName,
                'category_ids-suggest',
                '//*[@id="attribute-category_ids-container"]'
            );
        }
    }

    /**
     * Fills select category field
     *
     * @param string $name
     * @param string $elementId
     * @param string $parentLocation
     * @return void
     */
    protected function fillCategoryField($name, $elementId, $parentLocation)
    {
        // TODO should be removed after suggest widget implementation as typified element
        $this->_rootElement->find($elementId, Locator::SELECTOR_ID)->setValue($name);
        $this->waitForElementVisible(
            $parentLocation . '//div[@class="mage-suggest-dropdown"]',
            Locator::SELECTOR_XPATH
        );
        $this->_rootElement->find(
            $parentLocation . '//li[contains(@data-suggest-option, \'"label":"' . $name . '",\')]//a',
            Locator::SELECTOR_XPATH
        )->click();
    }

    /**
     * Save new category
     *
     * @param Product $fixture
     * @return void
     */
    public function addNewCategory(Product $fixture)
    {
        $this->openNewCategoryDialog();
        $this->_rootElement->find('input#new_category_name', Locator::SELECTOR_CSS)
            ->setValue($fixture->getNewCategoryName());

        $this->clearCategorySelect();
        $this->selectParentCategory();

        $this->_rootElement->find('div.ui-dialog-buttonset button.action-create')->click();
        $this->waitForElementNotVisible('div.ui-dialog-buttonset button.action-create');
    }

    /**
     * Select parent category for new one
     *
     * @return void
     */
    protected function selectParentCategory()
    {
        // TODO should be removed after suggest widget implementation as typified element
        $this->fillCategoryField(
            'Default Category',
            'new_category_parent-suggest',
            '//*[@id="new_category_form_fieldset"]'
        );
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
        $this->_rootElement->find('#add_category_button', Locator::SELECTOR_CSS)->click();
        $this->waitForElementVisible('input#new_category_name');
    }

    /**
     * Open tab
     *
     * @param string $tabName
     * @return Tab|bool
     */
    public function openTab($tabName)
    {
        $rootElement = $this->_rootElement;
        $selector = $this->tabs[$tabName]['selector'];
        $strategy = isset($this->tabs[$tabName]['strategy'])
            ? $this->tabs[$tabName]['strategy']
            : Locator::SELECTOR_CSS;
        $tab = $this->_rootElement->find($selector, $strategy);
        $advancedSettings = $this->_rootElement->find($this->advancedSettings);

        // Wait until all tabs will load
        $advancedTabList = $this->advancedTabList;
        $this->_rootElement->waitUntil(
            function () use ($rootElement, $advancedTabList) {
                return $rootElement->find($advancedTabList)->isVisible();
            }
        );

        if ($tab->isVisible()) {
            $tab->click();
        } elseif ($advancedSettings->isVisible()) {
            $advancedSettings->click();
            // Wait for open tab animation
            $tabPanel = $this->advancedTabPanel;
            $this->_rootElement->waitUntil(
                function () use ($rootElement, $tabPanel) {
                    return $rootElement->find($tabPanel, Locator::SELECTOR_XPATH)->isVisible();
                }
            );
            // Wait until needed tab will appear
            $this->_rootElement->waitUntil(
                function () use ($rootElement, $selector, $strategy, $tabPanel) {
                    $this->_rootElement->waitUntil(
                        function () use ($rootElement, $tabPanel) {
                            return $rootElement->find($tabPanel, Locator::SELECTOR_XPATH)->isVisible();
                        }
                    );
                    return $rootElement->find($selector, $strategy)->isVisible();
                }
            );
            $tab->click();
        } else {
            return false;
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
        $data = parent::getData($fixture);
        if ($fixture->hasData('status')) {
            $data['status'] = 'Product offline';
            if ($this->_rootElement->find(sprintf($this->onlineSwitcher, ':checked'))->isVisible()) {
                $data['status'] = 'Product online';
            }
        }

        return $data;
    }
}
