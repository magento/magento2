<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\App\View\Deployment;

/**
 * Deployment version of static files
 */
class Version
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Framework\App\View\Deployment\Version\StorageInterface
     */
    private $versionStorage;

    /**
     * @var \Magento\Framework\App\View\Deployment\Version\GeneratorInterface
     */
    private $versionGenerator;

    /**
     * @var string
     */
    private $cachedValue;

    /**
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\App\View\Deployment\Version\StorageInterface $versionStorage
     * @param \Magento\Framework\App\View\Deployment\Version\GeneratorInterface $versionGenerator
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\View\Deployment\Version\StorageInterface $versionStorage,
        \Magento\Framework\App\View\Deployment\Version\GeneratorInterface $versionGenerator
    ) {
        $this->appState = $appState;
        $this->versionStorage = $versionStorage;
        $this->versionGenerator = $versionGenerator;
    }

    /**
     * Retrieve deployment version of static files
     *
     * @return string
     */
    public function getValue()
    {
        if (!$this->cachedValue) {
            $this->cachedValue = $this->readValue($this->appState->getMode());
        }
        return $this->cachedValue;
    }

    /**
     * Load or generate deployment version of static files depending on the application mode
     *
     * @param string $appMode
     * @return string
     */
    protected function readValue($appMode)
    {
        switch ($appMode) {
            case \Magento\Framework\App\State::MODE_DEFAULT:
                try {
                    $result = $this->versionStorage->load();
                } catch (\UnexpectedValueException $e) {
                    $result = $this->versionGenerator->generate();
                    $this->versionStorage->save($result);
                }
                break;

            case \Magento\Framework\App\State::MODE_DEVELOPER:
                $result = $this->versionGenerator->generate();
                break;

            default:
                $result = $this->versionStorage->load();
        }
        return $result;
    }
}
