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
     * @var Magento\Framework\Composer\ComposerInformation
     */
    private $composerInfo;

    /**
     * Constructor
     *
     * @param MagentoComposerApplicationFactory $composerAppFactory
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
        $currentVersion = $systemPackageInfo['current_version'];
        foreach ($systemPackageInfo['available_versions'] as $version) {
            if (version_compare($currentVersion, $version, '<')) {
                $versions[] = ['id' => $version, 'name' => 'Version ' . $version];
            }
        }

        $versions[] = ['id' => $currentVersion, 'name' => 'Version ' . $currentVersion . ' (current)'];

        if (count($versions) > 1) {
            $versions[0]['name'] = $versions[0]['name'] . ' (latest)';
        }

        $result = [
            'package' => $systemPackageInfo['name'],
            'versions' => $versions
        ];

        return $result;
    }
}
