<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override\Fixture;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Annotation\AdminConfigFixture;
use Magento\TestFramework\Annotation\ConfigFixture;
use Magento\TestFramework\Annotation\DataFixture;
use Magento\TestFramework\Annotation\DataFixtureBeforeTransaction;
use Magento\TestFramework\Annotation\DataFixtureSetup;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\ConfigInterface;
use Magento\TestFramework\Workaround\Override\Fixture\Applier\AdminConfigFixture as AdminConfigFixtureApplier;
use Magento\TestFramework\Workaround\Override\Fixture\Applier\ApplierInterface;
use Magento\TestFramework\Workaround\Override\Fixture\Applier\Base;
use Magento\TestFramework\Workaround\Override\Fixture\Applier\ConfigFixture as ConfigFixtureApplier;
use Magento\TestFramework\Workaround\Override\Fixture\Applier\DataFixture as DataFixtureApplier;
use PHPUnit\Framework\TestCase;

/**
 * Class determines fixture applying according to configurations
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Resolver implements ResolverInterface
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var self */
    private static $instance;

    /** @var TestCase */
    private $currentTest;

    /** @var ConfigInterface */
    private $config;

    /** @var ApplierInterface[] */
    private $appliersList;

    /** @var string */
    private $currentFixtureType = null;

    /**
     * @var DataFixtureSetup
     */
    private $dataFixtureSetup;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->objectManager = Bootstrap::getObjectManager();
        $this->dataFixtureSetup = $this->objectManager->create(DataFixtureSetup::class);
    }

    /**
     * Get class instance
     *
     * @return ResolverInterface
     */
    public static function getInstance(): ResolverInterface
    {
        if (empty(self::$instance)) {
            throw new \RuntimeException('Override fixture resolver isn\'t initialized');
        }

        return self::$instance;
    }

    /**
     * Instance setter.
     *
     * @param ResolverInterface $instance
     * @return void
     */
    public static function setInstance(ResolverInterface $instance): void
    {
        self::$instance = $instance;
    }

    /**
     * @inheritdoc
     */
    public function setCurrentTest(?TestCase $currentTest): void
    {
        $this->currentTest = $currentTest;
    }

    /**
     * @inheritdoc
     */
    public function getCurrentTest(): ?TestCase
    {
        return $this->currentTest;
    }

    /**
     * @inheritdoc
     */
    public function setCurrentFixtureType(?string $fixtureType): void
    {
        $this->currentFixtureType = $fixtureType;
    }

    /**
     * @inheritdoc
     */
    public function requireDataFixture(string $path): void
    {
        if ($this->currentFixtureType === null) {
            throw new LocalizedException(__('Fixture type is not specified for resolver'));
        }
        /** @var DataFixtureApplier $dataFixtureApplier */
        $dataFixtureApplier = $this->getApplier($this->getCurrentTest(), $this->currentFixtureType);
        $this->dataFixtureSetup->apply(['factory' => $dataFixtureApplier->replace($path)]);
    }

    /**
     * @inheritdoc
     */
    public function applyConfigFixtures(TestCase $test, array $fixtures, string $fixtureType): array
    {
        $skipConfig = $this->config->getSkipConfiguration($test);

        return $skipConfig['skip']
            ? []
            : $this->getApplier($test, $fixtureType)->apply($fixtures);
    }

    /**
     * @inheritdoc
     */
    public function applyDataFixtures(TestCase $test, array $fixtures, string $fixtureType): array
    {
        $result = [];
        $skipConfig = $this->config->getSkipConfiguration($test);

        if (!$skipConfig['skip']) {
            $result = $this->getApplier($test, $fixtureType)->apply($fixtures);
        }

        return $result;
    }

    /**
     * Get appropriate fixture applier according to fixture type
     *
     * @param string $fixtureType
     * @return ApplierInterface
     */
    protected function getApplierByFixtureType(string $fixtureType): ApplierInterface
    {
        switch ($fixtureType) {
            case DataFixture::ANNOTATION:
            case DataFixtureBeforeTransaction::ANNOTATION:
                $applier = $this->objectManager->get(DataFixtureApplier::class);
                break;
            case ConfigFixture::ANNOTATION:
                $applier = $this->objectManager->get(ConfigFixtureApplier::class);
                break;
            case AdminConfigFixture::ANNOTATION:
                $applier = $this->objectManager->get(AdminConfigFixtureApplier::class);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported fixture type %s provided', $fixtureType));
        }

        return $applier;
    }

    /**
     * Get applier with prepared config by annotation type
     *
     * @param TestCase $test
     * @param string $fixtureType
     * @return ApplierInterface
     */
    private function getApplier(TestCase $test, string $fixtureType): ApplierInterface
    {
        if (!isset($this->appliersList[$fixtureType])) {
            $this->appliersList[$fixtureType] = $this->getApplierByFixtureType($fixtureType);
        }
        /** @var Base $applier */
        $applier = $this->appliersList[$fixtureType];
        $applier->setGlobalConfig($this->config->getGlobalConfig($fixtureType));
        $applier->setClassConfig($this->config->getClassConfig($test, $fixtureType));
        $applier->setMethodConfig($this->config->getMethodConfig($test, $fixtureType));
        $applier->setDataSetConfig(
            $test->dataName()
                ? $this->config->getDataSetConfig($test, $fixtureType)
                : []
        );

        return $applier;
    }
}
