<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup;

use Composer\Package\Version\VersionParser;
use Magento\Framework\Module\DbVersionInfo;

/**
 * Old Validator for database is used in order to support backward compatability of modules that are installed
 * in old way (with Install/Upgrade Schema/Data scripts)
 */
class OldDbValidator implements UpToDateValidatorInterface
{
    /**
     * @var DbVersionInfo
     */
    private $dbVersionInfo;

    /**
     * @param DbVersionInfo $dbVersionInfo
     */
    public function __construct(DbVersionInfo $dbVersionInfo)
    {
        $this->dbVersionInfo = $dbVersionInfo;
    }

    /**
     * @inheritdoc
     */
    public function getNotUpToDateMessage(): string
    {
        $genericMessage = '<info>The module code base doesn\'t match the DB schema and data.</info>' .
            PHP_EOL .
            '<info>Some modules use code versions newer or older than the database.</info>';
        $messages = [];
        $versionParser = new VersionParser();
        $codebaseUpdateNeeded = false;
        foreach ($this->dbVersionInfo->getDbVersionErrors() as $error) {
            if (!$codebaseUpdateNeeded && $error[DbVersionInfo::KEY_CURRENT] !== 'none') {
                // check if module code base update is needed
                $currentVersion = $versionParser->parseConstraints($error[DbVersionInfo::KEY_CURRENT]);
                $requiredVersion = $versionParser->parseConstraints('>' . $error[DbVersionInfo::KEY_REQUIRED]);
                if ($requiredVersion->matches($currentVersion)) {
                    $codebaseUpdateNeeded = true;
                };

                $messages[] = sprintf(
                    "<info>%20s %10s: %11s  ->  %-11s</info>",
                    $error[DbVersionInfo::KEY_MODULE],
                    $error[DbVersionInfo::KEY_TYPE],
                    $error[DbVersionInfo::KEY_CURRENT],
                    $error[DbVersionInfo::KEY_REQUIRED]
                );
            }
        }

        return implode(PHP_EOL, $messages) . ($codebaseUpdateNeeded ? $genericMessage : '');
    }

    /**
     * @return bool
     */
    public function isUpToDate(): bool
    {
        return empty($this->dbVersionInfo->getDbVersionErrors());
    }
}
