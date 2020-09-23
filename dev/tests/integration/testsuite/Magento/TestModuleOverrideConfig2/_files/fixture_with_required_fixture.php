<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\TestModuleOverrideConfig\Model\FixtureCallStorage;

$resolver = Resolver::getInstance();
$resolver->requireDataFixture('Magento/TestModuleOverrideConfig2/_files/fixture3_second_module.php');

/** @var FixtureCallStorage $fixtureStorage */
$fixtureStorage = Bootstrap::getObjectManager()->get(FixtureCallStorage::class);
$fixtureStorage->addFixtureToStorage(basename(__FILE__));
