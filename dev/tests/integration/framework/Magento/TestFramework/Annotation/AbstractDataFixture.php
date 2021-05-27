<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Fixture\DataFixtureDirectivesParser;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DataFixtureFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use PHPUnit\Framework\TestCase;

/**
 * Class consist of dataFixtures base logic
 */
abstract class AbstractDataFixture
{
    /**
     * Fixtures that have been applied
     *
     * @var array
     */
    protected $_appliedFixtures = [];

    /**
     * @var array
     */
    protected $fixtures = [];

    /**
     * Retrieve fixtures from annotation
     *
     * @param TestCase $test
     * @param string|null $scope
     * @return array
     */
    protected function _getFixtures(TestCase $test, $scope = null)
    {
        $annotationKey = $this->getAnnotation();

        if (!empty($this->fixtures[$annotationKey][$this->getTestKey($test)])) {
            return $this->fixtures[$annotationKey][$this->getTestKey($test)];
        }

        $resolver = Resolver::getInstance();
        $resolver->setCurrentFixtureType($annotationKey);
        $annotations = $scope === null ? $this->getAnnotations($test) : $test->getAnnotations()[$scope];
        $existingFixtures = [];
        $objectManager = Bootstrap::getObjectManager();
        $fixtureDirectivesParser = $objectManager->get(DataFixtureDirectivesParser::class);
        foreach ($annotations[$annotationKey] ?? [] as $fixture) {
            $existingFixtures[] = $fixtureDirectivesParser->parse($fixture);
        }

        /* Need to be applied even test does not have added fixtures because fixture can be added via config */
        $this->fixtures[$annotationKey][$this->getTestKey($test)] = $resolver->applyDataFixtures(
            $test,
            $existingFixtures,
            $annotationKey
        );

        return $this->fixtures[$annotationKey][$this->getTestKey($test)] ?? [];
    }

    /**
     * Get method annotations.
     *
     * Overwrites class-defined annotations.
     *
     * @param TestCase $test
     * @return array
     */
    protected function getAnnotations(TestCase $test): array
    {
        $annotations = $test->getAnnotations();
        return array_replace((array)$annotations['class'], (array)$annotations['method']);
    }

    /**
     * Execute fixture scripts if any
     *
     * @param array $fixtures
     * @param TestCase $test
     * @return void
     */
    protected function _applyFixtures(array $fixtures, TestCase $test)
    {
        /** @var TestsIsolation $testsIsolation */
        $testsIsolation = Bootstrap::getObjectManager()->get(TestsIsolation::class);
        $dbIsolationState = $this->getDbIsolationState($test);
        $testsIsolation->createDbSnapshot($test, $dbIsolationState);
        /* Execute fixture scripts */
        foreach ($fixtures as $fixture) {
            if (is_callable([get_class($test), $fixture['name']])) {
                $fixture['name'] = get_class($test) . '::' . $fixture['name'];
            }
            $fixture['result'] = $this->applyDataFixture($fixture);
            $this->_appliedFixtures[] = $fixture;
        }
        $resolver = Resolver::getInstance();
        $resolver->setCurrentFixtureType(null);
    }

    /**
     * Revert changes done by fixtures
     *
     * @param TestCase|null $test
     * @return void
     */
    protected function _revertFixtures(?TestCase $test = null)
    {
        $resolver = Resolver::getInstance();
        $resolver->setCurrentFixtureType($this->getAnnotation());
        $appliedFixtures = array_reverse($this->_appliedFixtures);
        foreach ($appliedFixtures as $fixture) {
            $this->revertDataFixture($fixture);
        }
        $this->_appliedFixtures = [];
        $resolver->setCurrentFixtureType(null);

        if (null !== $test) {
            /** @var TestsIsolation $testsIsolation */
            $testsIsolation = Bootstrap::getObjectManager()->get(
                TestsIsolation::class
            );
            $dbIsolationState = $this->getDbIsolationState($test);
            $testsIsolation->checkTestIsolation($test, $dbIsolationState);
        }
    }

    /**
     * Return is explicit set isolation state
     *
     * @param TestCase $test
     * @return array|null
     */
    protected function getDbIsolationState(TestCase $test)
    {
        $annotations = $this->getAnnotations($test);
        return $annotations[DbIsolation::MAGENTO_DB_ISOLATION] ?? null;
    }

    /**
     * Get uniq test cache key
     *
     * @param TestCase $test
     * @return string
     */
    private function getTestKey(TestCase $test): string
    {
        return sprintf('%s::%s', get_class($test), $test->getName());
    }

    /**
     * Get annotation name
     *
     * @return string
     */
    abstract protected function getAnnotation(): string;

    /**
     * Applies data fixture and returns the result.
     *
     * @param array $fixtureData
     * @return array|null
     */
    private function applyDataFixture(array $fixtureData): ?array
    {
        $dataFixtureFactory = Bootstrap::getObjectManager()->get(DataFixtureFactory::class);
        $fixture = $dataFixtureFactory->create($fixtureData['name']);
        $result = $fixture->apply($fixtureData['data'] ?? []);
        if ($result !== null && isset($fixtureData['identifier'])) {
            DataFixtureStorageManager::getStorage()->persist(
                $fixtureData['identifier'],
                Bootstrap::getObjectManager()->create(DataObject::class, ['data' => $result])
            );
        }
        return $result;
    }

    /**
     * Revert data fixture.
     *
     * @param array $fixtureData
     */
    private function revertDataFixture(array $fixtureData): void
    {
        $dataFixtureFactory = Bootstrap::getObjectManager()->get(DataFixtureFactory::class);
        $fixture = $dataFixtureFactory->create($fixtureData['name']);
        if ($fixture instanceof RevertibleDataFixtureInterface) {
            try {
                $fixture->revert($fixtureData['result'] ?? []);
            } catch (NoSuchEntityException $exception) {
                //ignore
            }
        }
    }
}
