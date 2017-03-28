<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Interface AreaInterface
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
     */
    public function load($partName = null);
}
