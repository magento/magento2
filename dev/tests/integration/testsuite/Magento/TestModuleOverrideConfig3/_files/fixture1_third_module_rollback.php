<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestModuleOverrideConfig\Model\FixtureCallStorage;

/** @var FixtureCallStorage $fixtureStorage */
$fixtureStorage = Bootstrap::getObjectManager()->get(FixtureCallStorage::class);
$fixtureStorage->clearStorage();
