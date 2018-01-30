<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model\Deploy;

interface DeployInterface
{
    /**
     * Base locale option without customizations
     */
    const DEPLOY_BASE_LOCALE = 'deploy_base_locale';

    /**
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @return int
     */
    public function deploy($area, $themePath, $locale);
}
