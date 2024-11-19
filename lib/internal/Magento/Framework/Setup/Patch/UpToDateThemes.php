<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Setup\Patch;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Setup\UpToDateValidatorInterface;
use Magento\Theme\Model\Config\Importer;

class UpToDateThemes implements UpToDateValidatorInterface
{
    private Importer $themeImporter;

    private DeploymentConfig $deploymentConfig;

    /**
     * UpToDateThemes constructor.
     *
     * @param Importer         $themeImporter
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        Importer $themeImporter,
        DeploymentConfig $deploymentConfig,
    ) {
        $this->themeImporter = $themeImporter;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @return string
     */
    public function getNotUpToDateMessage() : string
    {
        return 'Themes are not up to date';
    }

    /**
     * @return bool
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function isUpToDate() : bool
    {
        return !count($this->themeImporter->getWarningMessages($this->deploymentConfig->getConfigData('themes')));
    }
}
