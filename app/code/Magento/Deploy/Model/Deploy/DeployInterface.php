<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model\Deploy;

interface DeployInterface
{
    /**
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @return int
     */
    public function deploy($area, $themePath, $locale);
}
