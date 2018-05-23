<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Model\Config\Structure\ElementVisibility;

use Magento\Config\Model\Config\Structure\ElementVisibilityInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\Config\ConfigOptionsListConstants as Constants;

/**
 * Defines status of visibility of form elements on Stores > Settings > Configuration page
 * when Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION is enabled.
 * @api
 */
class ConcealInProductionWithoutScdOnDemand implements ElementVisibilityInterface
{
    /**
     * The list of form element paths with concrete visibility status.
     *
     * E.g.
     *
     * ```php
     * [
     *      'general/locale/code' => ElementVisibilityInterface::DISABLED,
     *      'general/country' => ElementVisibilityInterface::HIDDEN,
     * ];
     * ```
     *
     * It means that:
     *  - field Locale (in group Locale Options in section General) will be disabled
     *  - group Country Options (in section General) will be hidden
     *
     * @var array
     */
    private $configs = [];

    /**
     *
     * The list of form element paths which ignore visibility status.
     *
     * E.g.
     *
     * ```php
     * [
     *      'general/country/default' => '',
     * ];
     * ```
     *
     * It means that:
     *  - field 'default' in group Country Options (in section General) will be showed, even if all group(section)
     *    will be hidden.
     *
     * @var array
     */
    private $exemptions = [];

    /**
     * @var ConcealInProduction
     */
    private $concealInProduction;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param ConcealInProductionFactory $concealInProductionFactory
     * @param State $state The object that has information about the state of the system
     * @param DeploymentConfig $deploymentConfig Deployment configuration reader
     * @param array $configs The list of form element paths with concrete visibility status.
     * @param array $exemptions The list of form element paths which ignore visibility status.
     */
    public function __construct(
        ConcealInProductionFactory $concealInProductionFactory,
        DeploymentConfig $deploymentConfig,
        array $configs = [],
        array $exemptions = []
    ) {
        $this->concealInProduction = $concealInProductionFactory
            ->create(['configs' => $configs, 'exemptions' => $exemptions]);
        $this->deploymentConfig = $deploymentConfig;
        $this->configs = $configs;
        $this->exemptions = $exemptions;
    }

    /**
     * @inheritdoc
     */
    public function isHidden($path): bool
    {
        if (!$this->deploymentConfig->getConfigData(Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)) {
            return $this->concealInProduction->isHidden($path);
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isDisabled($path): bool
    {
        if (!$this->deploymentConfig->getConfigData(Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)) {
            return $this->concealInProduction->isDisabled($path);
        }
        return false;
    }
}
