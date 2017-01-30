<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product\View;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class CustomOptions
 * Form of custom options product
 */
class CustomOptions extends Form
{
    /**
     * Selector for options context
     *
     * @var string
     */
    protected $optionsContext = '#product-options-wrapper';

    /**
     * Selector for single option block
     *
     * @var string
     */
    protected $optionElement = '#product-options-wrapper > * > .field';

    /**
     * Selector for title of option
     *
     * @var string
     */
    protected $title = 'label > span:nth-child(1), legend > span:nth-child(1)';

    /**
     * Selector for required option
     *
     * @var string
     */
    protected $required = './self::*[contains(@class,"required")]';

    /**
     * Selector for price notice of option
     *
     * @var string
     */
    protected $priceNotice = './/*[@class="price-notice"]';

    /**
     * Selector for max characters of option
     *
     * @var string
     */
    protected $maxCharacters = './/div[@class="control"]/p[@class="note"]/strong';

    /**
     * Selector for label of option value element
     *
     * @var string
     */
    protected $optionLabel = './/div[@class="control"]//label[contains(@for, "options_")][%d]';

    /**
     * Select note of option by number
     *
     * @var string
     */
    protected $noteByNumber = './/*[@class="note"][%d]/strong';

    /**
     * Selector for select element of option
     *
     * @var string
     */
    protected $selectOption = './/div[@class="control"]/select';

    /**
     * Selector for option of select element
     *
     * @var string
     */
    protected $option = './/option[%d]';

    /**
     * Option XPath locator by value
     *
     * @var string
     */
    protected $optionByValueLocator = '//*[@class="product-options-wrapper"]//option[text()="%s"]/..';

    /**
     * Select XPath locator by title
     *
     * @var string
     */
    protected $selectByTitleLocator = '//*[*[@class="product-options-wrapper"]//span[text()="%s"]]//select';

    /**
     * Select XPath locator by option name
     *
     * @var string
     */
    protected $optionByName = '//*[label[contains(.,"%s")] or legend[contains(.,"%s")]]';

    /**
     * Get product options
     *
     * @param FixtureInterface $product
     * @return array
     * @throws \Exception
     */
    public function getOptions(FixtureInterface $product)
    {
        $dataOptions = $product->hasData('custom_options')
            ? $product->getDataFieldConfig('custom_options')['source']->getCustomOptions()
            : [];
        $listCustomOptions = $this->getListOptions();
        $result = [];

        foreach ($dataOptions as $option) {
            $title = $option['title'];
            if (!isset($listCustomOptions[$title])) {
                throw new \Exception("Can't find option: \"{$title}\"");
            }

            /** @var SimpleElement $optionElement */
            $optionElement = $listCustomOptions[$title];
            $typeMethod = preg_replace('/[^a-zA-Z]/i', '', $this->getOptionType($option['type']));
            $getTypeData = 'get' . ucfirst(strtolower($typeMethod)) . 'Data';

            $optionData = $this->$getTypeData($optionElement);
            $optionData['title'] = $title;
            $optionData['type'] = $option['type'];
            $optionData['is_require'] = $optionElement->find($this->required, Locator::SELECTOR_XPATH)->isVisible()
                ? 'Yes'
                : 'No';

            $result[$title] = $optionData;
        }

        return ['custom_options' => $result];
    }

    /**
     * Get list custom options
     *
     * @return array
     */
    protected function getListOptions()
    {
        $customOptions = [];

        $optionElements = $this->_rootElement->getElements($this->optionElement);
        foreach ($optionElements as $optionElement) {
            $title = $optionElement->find($this->title)->getText();
            $customOptions[$title] = $optionElement;
        }

        return $customOptions;
    }

    /**
     * Get data of "Field" custom option
     *
     * @param SimpleElement $option
     * @return array
     */
    protected function getFieldData(SimpleElement $option)
    {
        $price = $this->getOptionPriceNotice($option);
        $maxCharacters = $option->find($this->maxCharacters, Locator::SELECTOR_XPATH);

        return [
            'options' => [
                [
                    'price' => floatval($price),
                    'max_characters' => $maxCharacters->isVisible() ? $maxCharacters->getText() : null,
                ],
            ]
        ];
    }

    /**
     * Get data of "Area" custom option
     *
     * @param SimpleElement $option
     * @return array
     */
    protected function getAreaData(SimpleElement $option)
    {
        return $this->getFieldData($option);
    }

    /**
     * Get data of "File" custom option
     *
     * @param SimpleElement $option
     * @return array
     */
    protected function getFileData(SimpleElement $option)
    {
        $price = $this->getOptionPriceNotice($option);

        return [
            'options' => [
                [
                    'price' => floatval($price),
                    'file_extension' => $this->getOptionNotice($option, 1),
                    'image_size_x' => preg_replace('/[^0-9]/', '', $this->getOptionNotice($option, 2)),
                    'image_size_y' => preg_replace('/[^0-9]/', '', $this->getOptionNotice($option, 3)),
                ],
            ]
        ];
    }

    /**
     * Get data of "Drop-down" custom option
     *
     * @param SimpleElement $option
     * @return array
     */
    protected function getDropdownData(SimpleElement $option)
    {
        $select = $option->find($this->selectOption, Locator::SELECTOR_XPATH, 'select');
        // Skip "Choose option ..."(option #1)
        return $this->getSelectOptionsData($select, 2);
    }

    /**
     * Get data of "Multiple Select" custom option
     *
     * @param SimpleElement $option
     * @return array
     */
    protected function getMultipleSelectData(SimpleElement $option)
    {
        $multiselect = $option->find($this->selectOption, Locator::SELECTOR_XPATH, 'multiselect');
        return $this->getSelectOptionsData($multiselect, 1);
    }

    /**
     * Get data of "Radio Buttons" custom option
     *
     * @param SimpleElement $option
     * @return array
     */
    protected function getRadioButtonsData(SimpleElement $option)
    {
        $listOptions = [];

        $count = 1;
        /** @var SimpleElement $option */
        $option = $option->find(sprintf($this->optionLabel, $count), Locator::SELECTOR_XPATH);
        while ($option->isVisible()) {
            $listOptions[] = $this->parseOptionText($option->getText());
            ++$count;
            $option = $option->find(sprintf($this->optionLabel, $count), Locator::SELECTOR_XPATH);
        }

        return [
            'options' => $listOptions
        ];
    }

    /**
     * Get data of "Checkbox" custom option
     *
     * @param SimpleElement $option
     * @return array
     */
    protected function getCheckboxData(SimpleElement $option)
    {
        return $this->getRadioButtonsData($option);
    }

    /**
     * Get data of "Date" custom option
     *
     * @param SimpleElement $option
     * @return array
     */
    protected function getDateData(SimpleElement $option)
    {
        $price = $this->getOptionPriceNotice($option);

        return [
            'options' => [
                [
                    'price' => floatval($price),
                ],
            ]
        ];
    }

    /**
     * Get data of "Date & Time" custom option
     *
     * @param SimpleElement $option
     * @return array
     */
    protected function getDateTimeData(SimpleElement $option)
    {
        return $this->getDateData($option);
    }

    /**
     * Get data of "Time" custom option
     *
     * @param SimpleElement $option
     * @return array
     */
    protected function getTimeData(SimpleElement $option)
    {
        return $this->getDateData($option);
    }

    /**
     * Get data from option of select and multiselect
     *
     * @param SimpleElement $element
     * @param int $firstOption
     * @return array
     */
    protected function getSelectOptionsData(SimpleElement $element, $firstOption = 1)
    {
        $listOptions = [];

        $count = $firstOption;
        $selectOption = $element->find(sprintf($this->option, $count), Locator::SELECTOR_XPATH);
        while ($selectOption->isVisible()) {
            $listOptions[] = $this->parseOptionText($selectOption->getText());
            ++$count;
            $selectOption = $element->find(sprintf($this->option, $count), Locator::SELECTOR_XPATH);
        }

        return [
            'options' => $listOptions
        ];
    }

    /**
     * Get price from price-notice of custom option
     *
     * @param SimpleElement $option
     * @return array
     */
    protected function getOptionPriceNotice(SimpleElement $option)
    {
        $priceNotice = $option->find($this->priceNotice, Locator::SELECTOR_XPATH);
        if (!$priceNotice->isVisible()) {
            return null;
        }
        return preg_replace('/[^0-9\.]/', '', $priceNotice->getText());
    }

    /**
     * Get notice of option by number
     *
     * @param SimpleElement $option
     * @param int $number
     * @return mixed
     */
    protected function getOptionNotice(SimpleElement $option, $number)
    {
        $note = $option->find(sprintf($this->noteByNumber, $number), Locator::SELECTOR_XPATH);
        return $note->isVisible() ? $note->getText() : null;
    }

    /**
     * Parse option text to title and price
     *
     * @param string $optionText
     * @return array
     */
    protected function parseOptionText($optionText)
    {
        preg_match('`^(.*?) \+ ?\$([\d\.,]*?)$`', $optionText, $match);
        $optionPrice = isset($match[2]) ? str_replace(',', '', $match[2]) : 0;
        $optionTitle = isset($match[1]) ? trim($match[1]) : $optionText;

        return [
            'title' => $optionTitle,
            'price' => $optionPrice
        ];
    }

    /**
     * Fill custom options
     *
     * @param array $checkoutData
     * @return void
     */
    public function fillCustomOptions(array $checkoutData)
    {
        $checkoutOptions = $this->prepareOptions($checkoutData);
        $this->fillOptions($checkoutOptions);
    }

    /**
     * Prepare composite fields in checkout options data
     *
     * @param array $options
     * @return array
     */
    protected function prepareOptions(array $options)
    {
        $result = [];

        foreach ($options as $key => $option) {
            switch ($this->getOptionType($option['type'])) {
                case 'datetime':
                    list($day, $month, $year, $hour, $minute, $dayPart) = explode('/', $option['value']);
                    $option['value'] = [
                        'day' => $day,
                        'month' => $month,
                        'year' => $year,
                        'hour' => $hour,
                        'minute' => $minute,
                        'day_part' => $dayPart,
                    ];
                    break;
                case 'date':
                    list($day, $month, $year) = explode('/', $option['value']);
                    $option['value'] = [
                        'day' => $day,
                        'month' => $month,
                        'year' => $year,
                    ];
                    break;
                case 'time':
                    list($hour, $minute, $dayPart) = explode('/', $option['value']);
                    $option['value'] = [
                        'hour' => $hour,
                        'minute' => $minute,
                        'day_part' => $dayPart,
                    ];
                    break;
            }

            $result[$key] = $option;
        }

        return $result;
    }

    /**
     * Fill product options
     *
     * @param array $options
     * @return void
     */
    protected function fillOptions(array $options)
    {
        foreach ($options as $option) {
            $optionBlock = $this->_rootElement->find(
                sprintf($this->optionByName, $option['title'], $option['title']),
                Locator::SELECTOR_XPATH
            );
            $type = $this->getOptionType($option['type']);
            $mapping = $this->dataMapping([$type => $option['value']]);

            if ('radiobuttons' == $type || 'checkbox' == $type) {
                $mapping[$type]['selector'] = str_replace(
                    '%option_name%',
                    $mapping[$type]['value'],
                    $mapping[$type]['selector']
                );
                $mapping[$type]['value'] = 'Yes';
            }
            $this->_fill($mapping, $optionBlock);
        }
    }

    /**
     * Get customer option type
     *
     * @param string $option
     * @return string
     */
    protected function getOptionType($option)
    {
        $option = strpos($option, "/") !== false ? substr($option, strpos($option, "/") + 1) : $option;
        return strtolower(preg_replace('/[^a-z]/i', '', $option));
    }
}
