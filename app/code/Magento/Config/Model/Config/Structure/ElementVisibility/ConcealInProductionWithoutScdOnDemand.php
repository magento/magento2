<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Model\Config\Structure\ElementVisibility;

use Magento\Config\Model\Config\Structure\ElementVisibilityInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants as Constants;

/**
 * Defines status of visibility of form elements on Stores > Settings > Configuration page
 * when Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION is enabled
 * otherwise rule from Magento\Config\Model\Config\Structure\ElementVisibility\ConcealInProduction is used
 * @see \Magento\Config\Model\Config\Structure\ElementVisibility\ConcealInProduction
 *
 * @api
 * @since 101.0.6
 */
class ConcealInProductionWithoutScdOnDemand implements ElementVisibilityInterface
{
    /**
     * @var ConcealInProduction Element visibility rules in the Production mode
     */
    private $concealInProduction;

    /**
     * @var DeploymentConfig The application deployment configuration
     */
    private $deploymentConfig;

    /**
     * @param ConcealInProductionFactory $concealInProductionFactory
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
    }

    /**
     * @inheritdoc
     * @since 101.0.6
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
     * @since 101.0.6
     */
    public function isDisabled($path): bool
    {
        if (!$this->deploymentConfig->getConfigData(Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)) {
            return $this->concealInProduction->isDisabled($path);
        }
        return false;
    }
}
