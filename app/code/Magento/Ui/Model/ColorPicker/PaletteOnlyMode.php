<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Ui\Model\ColorPicker;

/**
 * Returns config parameters for full mode
 */
class PaletteOnlyMode implements ModeInterface
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'showInput' => false,
            'allowEmpty' => false,
            'showInitial' => false,
            'showPalette' => true,
            'showAlpha' => false,
            'showSelectionPalette' => true
        ];
    }
}
