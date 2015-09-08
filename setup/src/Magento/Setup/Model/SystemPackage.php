<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Composer\MagentoComposerApplication;
use Magento\Composer\InfoCommand;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Framework\Composer\ComposerInformation;

/**
 * Class SystemPackage returns system package and available for update versions
 */
class SystemPackage
{
    /**
     * @var InfoCommand
     */
    private $infoCommand;

    /**
     * @var MagentoComposerApplication
     */
    private $magentoComposerApplication;

    /**
     * @var ComposerInformation
     */
    private $composerInfo;

    /**
     * Constructor
     *
     * @param MagentoComposerApplicationFactory $composerAppFactory
     * @param ComposerInformation $composerInfo
     */
    public function __construct(
        MagentoComposerApplicationFactory $composerAppFactory,
        ComposerInformation $composerInfo
    ) {
        $this->infoCommand = $composerAppFactory->createInfoCommand();
        $this->magentoComposerApplication = $composerAppFactory->create();
        $this->composerInfo = $composerInfo;
    }

    /**
     * Returns system package and available versions
     *
     * @throws \RuntimeException
     * @return array
     */
    public function getPackageVersions()
    {
        $systemPackage = null;

        $locker = $this->magentoComposerApplication->createComposer()->getLocker();

        foreach ($locker->getLockedRepository()->getPackages() as $package) {
            $packageName = $package->getName();

            if ($this->composerInfo->isSystemPackage($packageName)) {
                $systemPackage = $packageName;
                break;
            }
        }

        $systemPackageInfo = $this->infoCommand->run($systemPackage);

        if (!$systemPackageInfo) {
            throw new \RuntimeException('System package not found');
        }

        $versions = [];

        foreach ($systemPackageInfo[InfoCommand::NEW_VERSIONS] as $version) {
            $versions[] = ['id' => $version, 'name' => 'Version ' . $version];
        }

        if ($systemPackageInfo[InfoCommand::CURRENT_VERSION]) {
            $versions[] = [
                'id' => $systemPackageInfo[InfoCommand::CURRENT_VERSION],
                'name' => 'Version ' . $systemPackageInfo[InfoCommand::CURRENT_VERSION]
            ];
        }

        if (count($versions) > 1) {
            $versions[0]['name'] .= ' (latest)';
        }

        if (count($versions) >= 1) {
            $versions[count($versions) - 1]['name'] .= ' (current)';
        }

        $result = [
            'package' => $systemPackageInfo['name'],
            'versions' => $versions
        ];

        return $result;
    }
}
