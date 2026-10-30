<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Ui\Model\ColorPicker;

/**
 * Returns config parameters for noalpha mode
 */
class NoAlphaMode implements ModeInterface
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'showInput' => true,
            'showInitial' => false,
            'showPalette' => true,
            'showAlpha' => false,
            'showSelectionPalette' => true
        ];
    }
}
