<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\TestFramework\Fixture\ParserInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use PHPUnit\Framework\TestCase;

/**
 * Class consist of dataFixtures base logic
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractDataFixture
{
    /**
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
        $parsers = Bootstrap::getObjectManager()
            ->create(
                \Magento\TestFramework\Annotation\Parser\Composite::class,
                [
                    'parsers' => $this->getParsers()
                ]
            );

        $fixtures = [];
        try {
            $fixtures = $parsers->parse($test, $scope ?: ParserInterface::SCOPE_METHOD);
            if (!$fixtures && !$scope) {
                $fixtures = $parsers->parse($test, ParserInterface::SCOPE_CLASS);
            }
        } catch (\Throwable $exception) {
            ExceptionHandler::handle(
                'Unable to parse fixtures',
                $exception,
                $test
            );
        }

        /* Need to be applied even test does not have added fixtures because fixture can be added via config */
        $this->fixtures[$annotationKey][$this->getTestKey($test)] = $resolver->applyDataFixtures(
            $test,
            $fixtures,
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
        $annotations = TestCaseAnnotation::getInstance()->getAnnotations($test);

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
        $objectManager = Bootstrap::getObjectManager();
        $testsIsolation = $objectManager->get(TestsIsolation::class);
        $dbIsolationState = $this->getDbIsolationState($test);
        $testsIsolation->createDbSnapshot($test, $dbIsolationState);
        $dataFixtureSetup = $objectManager->get(DataFixtureSetup::class);
        /* Execute fixture scripts */
        foreach ($fixtures as $fixture) {
            if (is_callable([get_class($test), $fixture['factory']])) {
                $fixture['factory'] = get_class($test) . '::' . $fixture['factory'];
            }
            $fixture['test'] = [
                'class' => get_class($test),
                'method' => $test->name(),
                'dataSet' => $test->dataName(),
            ];
            try {
                $fixture['result'] = $dataFixtureSetup->apply($fixture);
            } catch (\Throwable $exception) {
                ExceptionHandler::handle(
                    'Unable to apply fixture: ' . $this->getFixtureReference($fixture),
                    $exception,
                    $test
                );
            }
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
        $objectManager = Bootstrap::getObjectManager();
        $dataFixtureSetup = $objectManager->get(DataFixtureSetup::class);
        $resolver = Resolver::getInstance();
        $resolver->setCurrentFixtureType($this->getAnnotation());
        $appliedFixtures = array_reverse($this->_appliedFixtures);
        foreach ($appliedFixtures as $fixture) {
            try {
                $dataFixtureSetup->revert($fixture);
            } catch (\Throwable $exception) {
                ExceptionHandler::handle(
                    'Unable to revert fixture: ' . $this->getFixtureReference($fixture),
                    $exception,
                    $test
                );
            }
        }
        $this->_appliedFixtures = [];
        $resolver->setCurrentFixtureType(null);

        if (null !== $test) {
            /** @var TestsIsolation $testsIsolation */
            $testsIsolation = $objectManager->get(TestsIsolation::class);
            $dbIsolationState = $this->getDbIsolationState($test);
            $testsIsolation->checkTestIsolation($test, $dbIsolationState);
        }
    }

    /**
     * Get reference to the fixture definition
     *
     * @param array $fixture
     * @return string
     */
    private function getFixtureReference(array $fixture): string
    {
        return sprintf(
            '%s%s',
            $fixture['factory'],
            $fixture['name'] ? ' (' . $fixture['name'] . ')' : '',
        );
    }

    /**
     * Return fixtures parser
     *
     * @return ParserInterface[]
     */
    protected function getParsers(): array
    {
        $parsers = [];
        $parsers[] = Bootstrap::getObjectManager()->create(
            \Magento\TestFramework\Annotation\Parser\DataFixture::class,
            ['annotation' => $this->getAnnotation()]
        );
        return $parsers;
    }

    /**
     * Return is explicit set isolation state
     *
     * @param TestCase $test
     * @return array|null
     */
    protected function getDbIsolationState(TestCase $test)
    {
        $isEnabled = Bootstrap::getObjectManager()->get(DbIsolationState::class)->isEnabled($test);
        return $isEnabled === null ? null : [$isEnabled ? 'enabled' : 'disabled'];
    }

    /**
     * Get uniq test cache key
     *
     * @param TestCase $test
     * @return string
     */
    private function getTestKey(TestCase $test): string
    {
        return sprintf('%s::%s', get_class($test), $test->nameWithDataSet());
    }

    /**
     * Get annotation name
     *
     * @return string
     */
    abstract protected function getAnnotation(): string;
}
