<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Block\Adminhtml\Product\Attribute\Edit;

use Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit\AttributeForm;
use Magento\Mtf\Client\Locator;

/**
 * "create/update Swatch Product Attribute" Admin panel page.
 */
class SwatchAttributeForm extends AttributeForm
{
    /**
     * Locator to open Color Picker for specified Option Row.
     *
     * @var string
     */
    protected $colorPickerOpenLocator = '//tr[%s]/td[@class="swatches-visual-col col-default unavailable"]'
    . '/*[@class="swatch_window"]';

    /**
     * Locator to select "Choose a color" option in opened Color Picker Menu.
     *
     * @var string
     */
    protected $chooseColorButtonLocator = '/..//p[contains(., "Choose a color")]';

    /**
     * Locator to specify new Color Hex value.
     *
     * @var string
     */
    protected $hexInputLocator = '//*[@class="colorpicker"][%s]/*[@class="colorpicker_hex"]/input';

    /**
     * Locator to submit Color Picker.
     *
     * @var string
     */
    protected $submitColorLocator = '.colorpicker_submit';

    /**
     * "End" key code.
     */
    const END_KEY = "\xEE\x80\x90";

    /**
     * "Backspace" key code.
     */
    const BACKSPACE_KEY = "\xEE\x80\x83";

    /**
     * Apply specified Color for specified Option Row.
     * Merchant selects #000000 suggested by default if no exact Color is specified (workaround until MAGETWO-85304
     * is fixed & backported).
     *
     * @param int $optionKey
     * @param string $color
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function applyOptionColor($optionKey, $color)
    {
        $currentOptionPickerLocator = sprintf($this->colorPickerOpenLocator, $optionKey);
        $this->waitForElementVisible($currentOptionPickerLocator, Locator::SELECTOR_XPATH);
        $this->browser->find($currentOptionPickerLocator, Locator::SELECTOR_XPATH)->click();

        $chooseColorButtonLocator = $currentOptionPickerLocator . $this->chooseColorButtonLocator;
        $this->waitForElementVisible($chooseColorButtonLocator, Locator::SELECTOR_XPATH);
        $this->browser->find($chooseColorButtonLocator, Locator::SELECTOR_XPATH)->click();

        $this->waitForElementVisible($this->submitColorLocator);
        $this->browser->find($this->submitColorLocator)->click();
    }

    /**
     * Clear "Hex" input inside Color Picker & specify new Color.
     *
     * @param int $optionKey
     * @param string $color
     * @return void
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function specifyHex($optionKey, $color)
    {
        $currentHexInputLocator = sprintf($this->hexInputLocator, $optionKey);
        $this->waitForElementVisible($currentHexInputLocator, Locator::SELECTOR_XPATH);
        $hexInput = $this->browser->find($currentHexInputLocator, Locator::SELECTOR_XPATH);
        $hexInput->keys(
            [
                self::END_KEY,
                self::BACKSPACE_KEY,
                self::BACKSPACE_KEY,
                self::BACKSPACE_KEY,
                self::BACKSPACE_KEY,
                self::BACKSPACE_KEY,
                self::BACKSPACE_KEY
            ]
        );
        $hexInput->setValue($color);
    }
}
