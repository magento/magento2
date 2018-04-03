<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Ui\Component\Form\Element;

use Magento\Ui\Component\Form\Field;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class ColorPicker
 *
 * @api
 */
class ColorPicker extends AbstractElement
{
    const NAME = 'colorPicker';

    const DEFAULT_MODE = 'full';

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        \Magento\Ui\Model\ColorPicker\ColorModesProvider $modesProvider,
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->modesProvider = $modesProvider;
        parent::__construct($context, $components, $data);
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName(): string
    {
        return static::NAME;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $modes = $this->modesProvider->getModes();
        $colorPickerModeSetting = $this->getData('config/colorPickerMode');
        $colorFormatSetting = $this->getData('config/colorFormat');
        $colorPickerMode = $modes[$colorPickerModeSetting] ?? $modes[self::DEFAULT_MODE];
        $colorPickerMode['preferredFormat'] = $colorFormatSetting;
        $this->_data['config']['colorPickerConfig'] = $colorPickerMode;

        parent::prepare();

    }
}
