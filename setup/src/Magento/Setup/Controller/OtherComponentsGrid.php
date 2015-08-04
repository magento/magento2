<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Composer\InfoCommand;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Controller for other components grid on select version page
 */
class OtherComponentsGrid extends AbstractActionController
{
    /**
     * @var ComposerInformation
     */
    private $composerInformation;

    /**
     * @var InfoCommand
     */
    private $infoCommand;

    /**
     * @param ComposerInformation $composerInformation
     * @param MagentoComposerApplicationFactory $magentoComposerApplicationFactory
     */
    public function __construct(
        ComposerInformation $composerInformation,
        MagentoComposerApplicationFactory $magentoComposerApplicationFactory
    ) {
        $this->composerInformation = $composerInformation;
        $this->infoCommand = $magentoComposerApplicationFactory->createInfoCommand();
    }

    /**
     * Get Components from composer info command
     *
     * @return JsonModel
     * @throws \RuntimeException
     */
    public function componentsAction()
    {
        try {
            $components = $this->composerInformation->getInstalledMagentoPackages();
            $id = 0;
            foreach ($components as $component) {
                if (!$this->composerInformation->checkPackageInJson($component['name'])) {
                    unset($components[$component['name']]);
                    continue;
                }
                $vendor = explode('/', $component['name']);

                $packageInfo = $this->infoCommand->run($component['name']);
                if (!$packageInfo) {
                    throw new \RuntimeException('Package info not found for ' . $component['name']);
                }
                $currentVersion = $packageInfo[InfoCommand::CURRENT_VERSION];
                $components[$component['name']]['version'] = $currentVersion;
                $allVersions = explode(' ', $packageInfo[InfoCommand::VERSIONS]);
                $versions = [];
                $first = true;
                for ($i = 0; $i < count($allVersions); $i++) {
                    $allVersions[$i] = trim($allVersions[$i], ',');
                    $allVersions[$i] = trim(trim($allVersions[$i], '*'));
                    if ($allVersions[$i] === '') {
                        continue;
                    }
                    if ($allVersions[$i] !== $currentVersion) {
                        if ($first) {
                            $versions[] = $allVersions[$i] . ' (latest)';
                            $first = false;
                        } else {
                            $versions[] = $allVersions[$i];
                        }
                    } else {
                        $versions[] = $allVersions[$i];
                        break;
                    }
                }
                $components[$component['name']]['vendor'] = $vendor[0];
                $components[$component['name']]['updates'] = $versions;
                $components[$component['name']]['dropdownId'] = 'dd_' . $component['name'] . $id;
                $components[$component['name']]['checkboxId'] = 'cb_' . $component['name'] . $id;
            }
            return new JsonModel(
                [
                    'success' => true,
                    'components' => $components,
                    'total' => count($components),
                    'responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS
                ]
            );
        } catch (\Exception $e) {
            return new JsonModel(
                [
                    'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR
                ]
            );
        }
    }
}
