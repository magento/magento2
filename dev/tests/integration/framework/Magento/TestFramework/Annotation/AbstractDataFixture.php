<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\TestFramework\Fixture\ParserInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

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
        $parsers = $this->getParsers();
        $scopes = $scope ? [$scope] : [ParserInterface::SCOPE_METHOD, ParserInterface::SCOPE_CLASS];
        $fixtures = [];
        foreach ($scopes as $scp) {
            foreach ($parsers as $parser) {
                try {
                    array_push($fixtures, ...$parser->parse($test, $scp));
                } catch (\Throwable $exception) {
                    throw new Exception(
                        sprintf(
                            "Unable to parse fixtures\n#0 %s",
                            $this->getTestReference($this->getTestMetadata($test))
                        ),
                        $exception
                    );
                }
            }
            if (!empty($fixtures)) {
                break;
            }
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
            $fixture['test'] = $this->getTestMetadata($test);
            try {
                $fixture['result'] = $dataFixtureSetup->apply($fixture);
            } catch (\Throwable $exception) {
                throw new Exception(
                    sprintf(
                        "Unable to apply fixture: %s.\n#0 %s",
                        $this->getFixtureReference($fixture),
                        $this->getTestReference($fixture['test'])
                    ),
                    $exception
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
                throw new Exception(
                    sprintf(
                        "Unable to revert fixture: %s.\n#0 %s",
                        $this->getFixtureReference($fixture),
                        $this->getTestReference($fixture['test'])
                    ),
                    $exception
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
     * Returns information about the class name
     *
     * @param TestCase $test
     * @return array
     */
    private function getTestMetadata(TestCase $test): array
    {
        return [
            'class' => get_class($test),
            'method' => $test->getName(false),
            'dataSet' => $test->dataName(),
        ];
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
     * Get reference to the test definition
     *
     * @param array $testMetadata
     * @return string
     */
    private function getTestReference(array $testMetadata): string
    {
        try {
            $reflected = new ReflectionClass($testMetadata['class']);
        } catch (ReflectionException $e) {
            throw new \PHPUnit\Framework\Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        $name = $testMetadata['method'];

        if ($name && $reflected->hasMethod($name)) {
            try {
                $reflected = $reflected->getMethod($name);
            } catch (ReflectionException $e) {
                throw new \PHPUnit\Framework\Exception(
                    $e->getMessage(),
                    (int) $e->getCode(),
                    $e
                );
            }
        }

        return sprintf(
            "%s(%d): %s->%s()",
            $reflected->getFileName(),
            $reflected->getStartLine(),
            $testMetadata['class'],
            $testMetadata['method']
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
}
