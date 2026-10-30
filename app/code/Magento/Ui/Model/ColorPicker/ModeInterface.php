<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Ui\Model\ColorPicker;

/**
 * Mode interface for color modes
 *
 * @api
 */
interface ModeInterface
{
    /**
     * Returns config parameters for spectrum library
     *
     * @return array
     */
    public function getConfig(): array ;
}
