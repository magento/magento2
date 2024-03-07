<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Implementation of the @magentoSchemaFixture DocBlock annotation.
 */
namespace Magento\TestFramework\Annotation;

use PHPUnit\Util\Test as TestUtil;

/**
 * Represents following construction handling:
 *
 * @magentoSchemaFixture {link_to_file.php}
 */
class SchemaFixture
{
    /**
     * Fixtures base directory.
     *
     * @var string
     */
    protected $fixtureBaseDir;

    /**
     * Fixtures that have been applied.
     *
     * @var array
     */
    private $appliedFixtures = [];

    /**
     * Constructor.
     *
     * @param  string $fixtureBaseDir
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct($fixtureBaseDir)
    {
        if (!is_dir($fixtureBaseDir)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase("Fixture base directory '%1' does not exist.", [$fixtureBaseDir])
            );
        }
        $this->fixtureBaseDir = realpath($fixtureBaseDir);
    }

    /**
     * Apply magento data fixture on.
     *
     * @param  \PHPUnit\Framework\TestCase $test
     * @return void
     */
    public function startTest(\PHPUnit\Framework\TestCase $test)
    {
        if ($this->_getFixtures($test)) {
            $this->_applyFixtures($this->_getFixtures($test));
        }
    }

    /**
     * Finish test execution.
     *
     * @param \PHPUnit\Framework\TestCase $test
     */
    public function endTest(\PHPUnit\Framework\TestCase $test)
    {
        if ($this->_getFixtures($test)) {
            $this->_revertFixtures();
        }
    }

    /**
     * Retrieve fixtures from annotation.
     *
     * @param  \PHPUnit\Framework\TestCase $test
     * @param  string                      $scope
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getFixtures(\PHPUnit\Framework\TestCase $test, $scope = null)
    {
        if ($scope === null) {
            $annotations = $this->getAnnotations($test);
        } else {
            $source = TestUtil::parseTestMethodAnnotations(
                get_class($test),
                $test->getName(false)
            );
            $annotations = $source[$scope];
        }
        $result = [];
        if (!empty($annotations['magentoSchemaFixture'])) {
            foreach ($annotations['magentoSchemaFixture'] as $fixture) {
                if (strpos($fixture, '\\') !== false) {
                    // usage of a single directory separator symbol streamlines search across the source code
                    throw new \Magento\Framework\Exception\LocalizedException(
                        new \Magento\Framework\Phrase('Directory separator "\\" is prohibited in fixture declaration.')
                    );
                }
                $fixtureMethod = [get_class($test), $fixture];
                if (is_callable($fixtureMethod)) {
                    $result[] = $fixtureMethod;
                } else {
                    $result[] = $this->fixtureBaseDir . '/' . $fixture;
                }
            }
        }
        return $result;
    }

    /**
     * Get annotations for test.
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @return array
     */
    private function getAnnotations(\PHPUnit\Framework\TestCase $test)
    {
        $annotations = TestUtil::parseTestMethodAnnotations(
            get_class($test),
            $test->getName(false)
        );

        return array_replace($annotations['class'], $annotations['method']);
    }

    /**
     * Execute single fixture script.
     *
     * @param  string|array $fixture
     * @throws \Exception
     */
    protected function _applyOneFixture($fixture)
    {
        try {
            if (is_callable($fixture)) {
                call_user_func($fixture);
            } else {
                include $fixture;
            }
        } catch (\Exception $e) {
            throw new \Exception(
                sprintf("Error in fixture: %s.\n %s", json_encode($fixture), $e->getMessage()),
                500,
                $e
            );
        }
    }

    /**
     * Execute fixture scripts if any.
     *
     * @param  array $fixtures
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _applyFixtures(array $fixtures)
    {
        /* Execute fixture scripts */
        foreach ($fixtures as $oneFixture) {
            /* Skip already applied fixtures */
            if (in_array($oneFixture, $this->appliedFixtures, true)) {
                continue;
            }
            $this->_applyOneFixture($oneFixture);
            $this->appliedFixtures[] = $oneFixture;
        }
    }

    /**
     * Revert changes done by fixtures.
     */
    protected function _revertFixtures()
    {
        foreach ($this->appliedFixtures as $fixture) {
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
        $this->appliedFixtures = [];
    }
}
