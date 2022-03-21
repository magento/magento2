<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Patch;

/**
 * Allows to read all patches through the whole system
 */
class PatchRegistry implements \IteratorAggregate
{
    /**
     *
     * @var array
     */
    private $dependents = [];

    /**
     * @var string[]
     */
    private $patches = [];

    /**
     * @var PatchFactory
     */
    private $patchFactory;

    /**
     * This classes need to do revert
     *
     * @var string[]
     */
    private $appliedPatches = [];

    /**
     * @var PatchHistory
     */
    private $patchHistory;

    /**
     * @var \Iterator
     */
    private $iterator = null;

    /**
     * @var \Iterator
     */
    private $reverseIterator = null;

    /**
     * @var array
     */
    private $cyclomaticStack = [];

    /**
     * PatchRegistry constructor.
     * @param PatchFactory $patchFactory
     * @param PatchHistory $patchHistory
     */
    public function __construct(PatchFactory $patchFactory, PatchHistory $patchHistory)
    {
        $this->patchFactory = $patchFactory;
        $this->patchHistory = $patchHistory;
    }

    /**
     * Register all dependents to patch
     *
     * @param string|DependentPatchInterface $patchName
     */
    private function registerDependents(string $patchName)
    {
        $dependencies = $patchName::getDependencies();

        foreach ($dependencies as $dependency) {
            $this->dependents[$dependency][] = $patchName;
        }
    }

    /**
     * Register patch and create chain of patches
     *
     * @param string $patchName
     * @return PatchInterface | bool
     */
    public function registerPatch(string $patchName)
    {
        if ($this->patchHistory->isApplied($patchName)) {
            $this->appliedPatches[$patchName] = $patchName;
            $this->registerDependents($patchName);
            return false;
        }

        if (isset($this->patches[$patchName])) {
            return $this->patches[$patchName];
        }

        $this->patches[$patchName] = $patchName;
        return $patchName;
    }

    /**
     * Retrieve all patches, that depends on current one
     *
     * @param string $patch
     * @return string[]
     */
    private function getDependentPatches(string $patch)
    {
        $patches = [];
        $patchName = $patch;

        /**
         * Let`s check if patch is dependency for other patches
         */
        if (isset($this->dependents[$patchName])) {
            foreach ($this->dependents[$patchName] as $dependent) {
                if (isset($this->appliedPatches[$dependent])) {
                    $dependent = $this->appliedPatches[$dependent];
                    $patches = array_replace($patches, $this->getDependentPatches($dependent));
                    $patches[$dependent] = $dependent;
                    unset($this->appliedPatches[$dependent]);
                }
            }
        }

        return $patches;
    }

    /**
     * Get patch dependencies.
     *
     * @param string $patch
     * @return string[]
     */
    private function getDependencies(string $patch)
    {
        $depInstances = [];
        $deps = call_user_func([$patch, 'getDependencies']);
        $this->cyclomaticStack[$patch] = true;

        foreach ($deps as $dep) {
            if (isset($this->cyclomaticStack[$dep])) {
                throw new \LogicException("Cyclomatic dependency during patch installation");
            }

            $depInstance = $this->registerPatch($dep);
            /**
             * If a patch already have applied dependency - then we definitely know
             * that all other dependencies in dependency chain are applied too, so we can skip this dep
             */
            if (!$depInstance) {
                continue;
            }

            $depInstances = array_replace($depInstances, $this->getDependencies($this->patches[$dep]));
            $depInstances[$depInstance] = $depInstance;
        }

        unset($this->cyclomaticStack[$patch]);
        return $depInstances;
    }

    /**
     * If you want to uninstall system, there you will run all patches in reverse order
     *
     * But note, that patches also have dependencies, and if patch is dependency to any other patch
     * you will to revert it dependencies first and only then patch
     *
     * @return \ArrayIterator
     */
    public function getReverseIterator()
    {
        if ($this->reverseIterator === null) {
            $reversePatches = [];

            while (!empty($this->appliedPatches)) {
                $patch = array_pop($this->appliedPatches);
                $reversePatches = array_replace($reversePatches, $this->getDependentPatches($patch));
                $reversePatches[$patch] = $patch;
            }

            $this->reverseIterator = new \ArrayIterator($reversePatches);
        }

        return $this->reverseIterator;
    }

    /**
     * Retrieve iterator of all patch instances
     *
     * If patch have dependencies, then first of all dependencies should be installed and only then desired patch
     *
     * @return \ArrayIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        if ($this->iterator === null) {
            $installPatches = [];
            $patchInstances = $this->patches;

            while (!empty($patchInstances)) {
                $firstPatch = array_shift($patchInstances);
                $deps = $this->getDependencies($firstPatch);

                /**
                 * Remove deps from patchInstances
                 */
                foreach ($deps as $dep) {
                    unset($patchInstances[$dep]);
                }

                $installPatches = array_replace($installPatches, $deps);
                $installPatches[$firstPatch] = $firstPatch;
            }

            $this->iterator = new \ArrayIterator($installPatches);
        }

        return $this->iterator;
    }
}
