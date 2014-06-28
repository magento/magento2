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

/**
 * Class Custom Options
 * Block of custom options product
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class CustomOptions extends Block
{
    /**
     * Regexp price pattern
     *
     * @var string
     */
    protected $pricePattern = '#\$([\d,]+\.\d+)$#';

    /**
     * Field set XPath locator
     *
     * @var string
     */
    protected $fieldsetLocator = '//*[@id="product-options-wrapper"]//*[@class="fieldset"]';

    /**
     * Field XPath locator
     *
     * @var string
     */
    protected $fieldLocator = '/div[not(contains(@class,"downloads")) and contains(@class,"field")%s][%d]';

    /**
     * Required field XPath locator
     *
     * @var string
     */
    protected $requiredLocator = ' and contains(@class,"required")';

    /**
     * Select field XPath locator
     *
     * @var string
     */
    protected $selectLocator = './div[contains(@class,"control")]//select';

    /**
     * Title value CSS locator
     *
     * @var string
     */
    protected $titleLocator = '.label span:not(.price-notice)';

    /**
     * Price value CSS locator
     *
     * @var string
     */
    protected $priceLocator = '.label .price-notice';

    /**
     * Option XPath locator
     *
     * @var string
     */
    protected $optionLocator = './option[%d]';

    /**
     * Option XPath locator by value
     *
     * @var string
     */
    protected $optionByValueLocator = '//*[@class="product options wrapper"]//option[text()="%s"]/..';

    /**
     * Select XPath locator by title
     *
     * @var string
     */
    protected $selectByTitleLocator = '//*[*[@class="product options wrapper"]//span[text()="%s"]]//select';

    /**
     * Bundle field CSS locator
     *
     * @var string
     */
    protected $bundleFieldLocator = '#product-options-wrapper > .fieldset > .field';

    /**
     * Get product options
     *
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        $index = 1;

        $fieldElement = $this->_rootElement->find(
            $this->fieldsetLocator . sprintf($this->fieldLocator, '', $index),
            Locator::SELECTOR_XPATH
        );

        while ($fieldElement && $fieldElement->isVisible()) {
            $option = ['price' => []];
            $option['is_require'] = $this->_rootElement->find(
                $this->fieldsetLocator . sprintf($this->fieldLocator, $this->requiredLocator, $index),
                Locator::SELECTOR_XPATH
            )->isVisible();
            $option['title'] = $fieldElement->find($this->titleLocator)->getText();

            if (($price = $fieldElement->find($this->priceLocator))
                && $price->isVisible()
            ) {
                $matches = [];
                $value = $price->getText();
                if (preg_match($this->pricePattern, $value, $matches)) {
                    $option['value'][] = $value;
                    $option['price'][] = $matches[1];
                }
            } elseif (($prices = $fieldElement->find(
                $this->selectLocator,
                Locator::SELECTOR_XPATH
            )
                ) && $prices->isVisible()
            ) {
                $priceIndex = 0;
                while (($price = $prices->find(sprintf($this->optionLocator, ++$priceIndex), Locator::SELECTOR_XPATH))
                    && $price->isVisible()
                ) {
                    $matches = [];
                    $value = $price->getText();
                    if (preg_match($this->pricePattern, $value, $matches)) {
                        $option['value'][] = $value;
                        $option['price'][] = $matches[1];
                    }
                }
            }
            $options[$option['title']] = $option;
            ++$index;
            $fieldElement = $this->_rootElement->find(
                $this->fieldsetLocator . sprintf($this->fieldLocator, '', $index),
                Locator::SELECTOR_XPATH
            );
        }

        return $options;
    }

    /**
     * Fill configurable product options
     *
     * @param array $productOptions
     * @return void
     */
    public function fillProductOptions($productOptions)
    {
        foreach ($productOptions as $attributeLabel => $attributeValue) {
            $select = $this->_rootElement->find(
                sprintf($this->selectByTitleLocator, $attributeLabel),
                Locator::SELECTOR_XPATH,
                'select'
            );
            $select->setValue($attributeValue);
        }
    }

    /**
     * Choose custom option in a drop down
     *
     * @param string $productOption
     * @return void
     */
    public function selectProductCustomOption($productOption)
    {
        $select = $this->_rootElement->find(
            sprintf($this->optionByValueLocator, $productOption),
            Locator::SELECTOR_XPATH,
            'select'
        );
        $select->setValue($productOption);
    }
}
