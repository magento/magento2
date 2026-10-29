<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Ui\Model\ColorPicker;

/**
 * Returns config parameters for full mode
 */
class FullMode implements ModeInterface
{
    /**
     * {@inheritdoc}
     *
     */
    public function getConfig(): array
    {
        return [
            'showInput' => true,
            'showInitial' => false,
            'showPalette' => true,
            'showAlpha' => true,
            'showSelectionPalette' => true
        ];
    }
}
