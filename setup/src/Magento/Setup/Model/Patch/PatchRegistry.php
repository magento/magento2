<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Patch;

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
     * @var PatchInterface[]
     */
    private $patchInstances = [];

    /**
     * @var PatchFactory
     */
    private $patchFactory;

    /**
     * @var PatchHistory
     */
    private $patchHistory;

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
     * Register patch and create chain of patches
     *
     * @param string $patchName
     * @return PatchInterface | bool
     */
    public function registerPatch(string $patchName)
    {
        if ($this->patchHistory->isApplied($patchName)) {
            return false;
        }

        if (isset($this->patchInstances[$patchName])) {
            return $this->patchInstances[$patchName];
        }

        $patch = $this->patchFactory->create($patchName);
        $this->patchInstances[$patchName] = $patch;
        $dependencies = $patch::getDependencies();

        foreach ($dependencies as $dependency) {
            $this->dependents[$dependency][] = $patchName;
        }

        return $patch;
    }

    /**
     * Retrieve all patches, that depends on current one
     *
     * @param PatchInterface $patch
     * @return PatchInterface[]
     */
    private function getDependentPatches(PatchInterface $patch)
    {
        $patches = [];
        $patchName = get_class($patch);

        if (isset($this->dependents[$patchName])) {
            foreach ($this->dependents[$patchName] as $dependentPatchName) {
                $patches[] = $this->patchInstances[$dependentPatchName];
                $patches += $this->getDependentPatches($this->patchInstances[$dependentPatchName]);
            }
        }

        return $patches;
    }

    /**
     * @param PatchInterface $patch
     * @return PatchInterface[]
     */
    private function getDependencies(PatchInterface $patch)
    {
        $depInstances = [];
        $deps = $patch::getDependencies();

        foreach ($deps as $dep) {
            $depInstance = $this->registerPatch($dep);
            /**
             * If a patch already have applied dependency - than we definently know
             * that all other dependencies in dependency chain are applied too, so we can skip this dep
             */
            if (!$depInstance) {
                continue;
            }

            $depInstances[] = $depInstance;
            $depInstances += $this->getDependencies($this->patchInstances[$dep]);
        }

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
        $reversePatches = [];

        while (!empty($this->patchInstances)) {
            $lastPatch = array_pop($this->patchInstances);
            $reversePatches += $this->getDependentPatches($lastPatch);
            $reversePatches[] = $lastPatch;
        }

        return new \ArrayIterator($reversePatches);
    }

    /**
     * Retrieve iterator of all patch instances
     *
     * If patch have dependencies, than first of all dependencies should be installed and only then desired patch
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        $installPatches = [];

        while (!empty($this->patchInstances)) {
            $firstPatch = array_shift($this->patchInstances);
            $installPatches = $this->getDependencies($firstPatch);
            $installPatches[] = $firstPatch;
        }

        return new \ArrayIterator($installPatches);
    }
}
