<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Composer\MagentoComposerApplication;
use Magento\Composer\InfoCommand;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;

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
     * Constructor
     *
     * @param MagentoComposerApplicationFactory $composerAppFactory
     */
    public function __construct(
        MagentoComposerApplicationFactory $composerAppFactory
    ) {
        $this->infoCommand = $composerAppFactory->createInfoCommand();
        $this->magentoComposerApplication = $composerAppFactory->create();
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

            if (preg_match('/magento\/product-*/', $packageName)) {
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
                $versions[] = ['id' => $version, 'name' => $version];
            }
        }

        $versions[] = ['id' => $currentVersion, 'name' => $currentVersion . ' (current)'];

        $result = [
            'package' => $systemPackageInfo['name'],
            'versions' => $versions
        ];

        return $result;
    }
}
