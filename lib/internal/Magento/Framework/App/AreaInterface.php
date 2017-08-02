<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Interface AreaInterface
 * @since 2.0.0
 */
interface AreaInterface
{
    const PART_CONFIG = 'config';
    const PART_TRANSLATE = 'translate';
    const PART_DESIGN = 'design';

    /**
     * Load area part
     *
     * @param string $partName
     * @return $this
     * @since 2.0.0
     */
    public function load($partName = null);
}
