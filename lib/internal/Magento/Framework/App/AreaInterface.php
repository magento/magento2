<?php
/**
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

/**
 * Interface AreaInterface
 *
 * @api
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
