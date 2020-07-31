<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use PHPUnit\Framework\Exception;
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
        $existingFixtures = $annotations[$annotationKey] ?? [];
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
        return array_replace($annotations['class'], $annotations['method']);
    }

    /**
     * Execute single fixture script
     *
     * @param string|array $fixture
     * @return void
     * @throws \Exception
     */
    protected function _applyOneFixture($fixture)
    {
        try {
            if (is_callable($fixture)) {
                call_user_func($fixture);
            } else {
                require $fixture;
            }
        } catch (\Exception $e) {
            throw new Exception(
                sprintf(
                    "Error in fixture: %s.\n %s\n %s",
                    json_encode($fixture),
                    $e->getMessage(),
                    $e->getTraceAsString()
                ),
                500,
                $e
            );
        }
    }

    /**
     * Execute fixture scripts if any
     *
     * @param array $fixtures
     * @return void
     * @throws LocalizedException
     */
    protected function _applyFixtures(array $fixtures)
    {
        /* Execute fixture scripts */
        foreach ($fixtures as $oneFixture) {
            $this->_applyOneFixture($oneFixture);
            $this->_appliedFixtures[] = $oneFixture;
        }
        $resolver = Resolver::getInstance();
        $resolver->setCurrentFixtureType(null);
    }

    /**
     * Revert changes done by fixtures
     *
     * @return void
     */
    protected function _revertFixtures()
    {
        $resolver = Resolver::getInstance();
        $resolver->setCurrentFixtureType($this->getAnnotation());
        $appliedFixtures = array_reverse($this->_appliedFixtures);
        foreach ($appliedFixtures as $fixture) {
            if (is_callable($fixture)) {
                $fixture[1] .= 'Rollback';
                if (is_callable($fixture)) {
                    $this->_applyOneFixture($fixture);
                }
            } else {
                $fileInfo = pathinfo($fixture);
                $extension = '';
                if (isset($fileInfo['extension'])) {
                    $extension = '.' . $fileInfo['extension'];
                }
                $rollbackScript = $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '_rollback' . $extension;
                if (file_exists($rollbackScript)) {
                    $this->_applyOneFixture($rollbackScript);
                }
            }
        }
        $this->_appliedFixtures = [];
        $resolver->setCurrentFixtureType(null);
    }

    /**
     * Return is explicit set isolation state
     *
     * @param TestCase $test
     * @return bool|null
     */
    protected function getDbIsolationState(TestCase $test)
    {
        $annotations = $this->getAnnotations($test);
        return isset($annotations[DbIsolation::MAGENTO_DB_ISOLATION])
            ? $annotations[DbIsolation::MAGENTO_DB_ISOLATION]
            : null;
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
