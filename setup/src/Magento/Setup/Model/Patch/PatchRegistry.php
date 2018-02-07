<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Patch;

/**
 * Allows to read all patches through the whole system
 */
class PatchRegistry
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
     * PatchRegistry constructor.
     * @param PatchFactory $patchFactory
     */
    public function __construct(PatchFactory $patchFactory)
    {
        $this->patchFactory = $patchFactory;
    }

    /**
     * Register patch and create chain of patches
     *
     * @param string $patchName
     * @return PatchInterface
     */
    public function registerPatch(string $patchName)
    {
        if (isset($this->patchInstances[$patchName])) {
            return $this->patchInstances[$patchName];
        }

        $patch = $this->patchFactory->create($patchName);
        $this->patchInstances[$patchName] = $patch;
        $dependencies = $patch->getDependencies();

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
    public function getDependentPatches(PatchInterface $patch)
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
    public function getDependencies(PatchInterface $patch)
    {
        $depInstances = [];
        $deps = $patch->getDependencies();

        foreach ($deps as $dep) {
            $depInstances[] = $this->registerPatch($dep);
            $depInstances += $this->getDependencies($this->patchInstances[$dep]);
        }

        return $depInstances;
    }
}
