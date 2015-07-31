<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Composer\InfoCommand;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Setup\Model\SystemPackage;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Controller for selecting version
 */
class SelectVersion extends AbstractActionController
{
    /**
     * @var SystemPackage
     */
    protected $systemPackage;

    /**
     * @var ComposerInformation
     */
    private $composerInformation;

    /**
     * @var InfoCommand
     */
    private $infoCommand;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param SystemPackage $systemPackage
     * @param ComposerInformation $composerInformation
     * @param MagentoComposerApplicationFactory $magentoComposerApplicationFactory
     * @param DirectoryList $directoryList
     */
    public function __construct(
        SystemPackage $systemPackage,
        ComposerInformation $composerInformation,
        MagentoComposerApplicationFactory $magentoComposerApplicationFactory,
        DirectoryList $directoryList
    ) {
        $this->systemPackage = $systemPackage;
        $this->composerInformation = $composerInformation;
        $this->infoCommand = $magentoComposerApplicationFactory->createInfoCommand();
        $this->directoryList = $directoryList;
    }
    /**
     * @return ViewModel|\Zend\Http\Response
     */
    public function indexAction()
    {
        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('/magento/setup/select-version.phtml');
        return $view;
    }

    /**
     * Gets system package and versions
     *
     * @return JsonModel
     */
    public function systemPackageAction()
    {
        $data = [];
        try {
            $data['package'] = $this->systemPackage->getPackageVersions();
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;
        } catch (\Exception $e) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
        }
        $data['responseType'] = $responseType;
        return new JsonModel($data);
    }

    /**
     * Get Components info action
     *
     * @return JsonModel
     */
    public function componentsAction()
    {
        try {
            $components = $this->composerInformation->getRootRequiredPackageTypesByNameVersion('upgrader');
            foreach ($components as $component) {
                if (!$this->checkPackageInJson($component['name'])) {
                    unset($components[$component['name']]);
                    continue;
                }
                $vendor = explode('/', $component['name']);
                $allVersions = $this->infoCommand->run($component['name']);
                $versions = explode(' ', $allVersions['versions']);
                array_walk($versions, function (&$item) {
                    $item = trim($item, ',');
                });
                unset($versions[0]);
                $upgradeVersions = [];
                $firstIndex = true;
                foreach ($versions as $version) {
                    if ($version !== $component['version']) {
                        if ($firstIndex) {
                            $upgradeVersions[] = $version . ' (latest)';
                            $firstIndex = false;
                        } else {
                            $upgradeVersions[] = $version;
                        }
                    } else {
                        break;
                    }
                }
                $components[$component['name']] ['author']= $vendor[0];
                $components[$component['name']] ['upgrades']= $upgradeVersions;
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

    /**
     * Checks if the package is inside the root composer or not
     *
     * @param string $name
     * @return bool
     */
    private function checkPackageInJson($name)
    {
        $jsonFile = file_get_contents($this->directoryList->getRoot() . '/composer.json');
        $jsonArray = json_decode($jsonFile, true);
        if (in_array($name, array_keys($jsonArray['require']))
            || in_array($name, array_keys($jsonArray['require-dev']))
        ) {
            return true;
        } else {
            false;
        }
    }
}
