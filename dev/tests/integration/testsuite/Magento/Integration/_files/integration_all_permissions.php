<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $integration \Magento\Integration\Model\Integration */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$integration = $objectManager->create(\Magento\Integration\Model\Integration::class);
$integration->setName('Fixture Integration')->save();

/** Grant permissions to integrations */
/** @var \Magento\Integration\Api\AuthorizationServiceInterface */
$authorizationService = $objectManager->create(\Magento\Integration\Api\AuthorizationServiceInterface::class);
$authorizationService->grantAllPermissions($integration->getId());
