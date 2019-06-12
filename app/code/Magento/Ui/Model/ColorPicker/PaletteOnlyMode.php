<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Ui\Model\ColorPicker;

/**
 * Returns config parameters for palette only mode
 */
class PaletteOnlyMode implements ModeInterface
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'showInput' => false,
            'showInitial' => false,
            'showPalette' => true,
            'showPaletteOnly' => true,
            'showAlpha' => false,
            'showSelectionPalette' => false
        ];
    }
}
