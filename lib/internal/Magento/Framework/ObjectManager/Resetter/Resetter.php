<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Resetter;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\ObjectManagerInterface;
use WeakMap;

/**
 * Class that keeps track of the instances that need to be reset, and resets them
 */
class Resetter implements ResetterInterface
{
    public const RESET_PATH = 'reset.json';
    private const RESET_STATE_METHOD = '_resetState';

    /** @var WeakMap instances to be reset after request */
    private WeakMap $resetAfterWeakMap;

    /** @var ObjectManagerInterface Note: We use temporal coupling here because of chicken/egg during bootstrapping */
    private ObjectManagerInterface $objectManager;

    /** @var WeakMapSorter|null Note: We use temporal coupling here because of chicken/egg during bootstrapping */
    private ?WeakMapSorter $weakMapSorter = null;

    /** @var array */
    private array $reflectionCache = [];

    /** @var array */
    private array $isObjectInClassListCache = [];

    /** @var array */
    private readonly array $classList;

    /** @var array */
    private array $sortedClassListsByClass = [];

    /**
     * @param ComponentRegistrarInterface|null $componentRegistrar
     * @param array $classList
     * @return void
     * @phpcs:disable Magento2.Functions.DiscouragedFunction
     */
    public function __construct(
        private ?ComponentRegistrarInterface $componentRegistrar = null,
        array $classList = [],
    ) {
        if (null === $this->componentRegistrar) {
            $this->componentRegistrar = new ComponentRegistrar();
        }
        foreach ($this->getPaths() as $resetPath) {
            if (!\file_exists($resetPath)) {
                continue;
            }
            $resetData = \json_decode(\file_get_contents($resetPath), true);
            if (!$resetData) {
                throw new LocalizedException(__('Error parsing %1', $resetPath));
            }
            $classList += $resetData;
        }
        $this->classList = $classList;
        $this->resetAfterWeakMap = new WeakMap;
    }

    /**
     * Get paths for reset json
     *
     * @return \Generator<string>
     */
    private function getPaths(): \Generator
    {
        yield BP . '/app/etc/' . self::RESET_PATH;
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $modulePath) {
            yield $modulePath . '/etc/' . self::RESET_PATH;
        }
    }

    /**
     * Add instance to be reset later
     *
     * @param object $instance
     * @return void
     */
    public function addInstance(object $instance) : void
    {
        if ($instance instanceof ResetAfterRequestInterface
            || \method_exists($instance, self::RESET_STATE_METHOD)
            || $this->isObjectInClassList($instance)
        ) {
            $this->resetAfterWeakMap[$instance] = true;
        }
    }

    /**
     * Reset state for all instances that we've created
     *
     * @return void
     * @throws \ReflectionException
     */
    public function _resetState(): void
    {
        /* Note: We force garbage collection to clean up cyclic referenced objects before _resetState()
         * This is to prevent calling _resetState() on objects that will be destroyed by garbage collector. */
        gc_collect_cycles();
        if (!$this->weakMapSorter) {
            $this->weakMapSorter = $this->objectManager->get(WeakMapSorter::class);
        }
        foreach ($this->weakMapSorter->sortWeakMapIntoWeakReferenceList($this->resetAfterWeakMap) as $weakReference) {
            $instance = $weakReference->get();
            if (!$instance) {
                continue;
            }
            if (!$instance instanceof ResetAfterRequestInterface) {
                $this->resetStateWithReflection($instance);
            } else {
                $instance->_resetState();
            }
        }
        /* Note: We must force garbage collection to clean up cyclic referenced objects after _resetState()
         * Otherwise, they may still show up in the WeakMap. */
        gc_collect_cycles();
    }

    /**
     * @inheritDoc
     */
    public function setObjectManager(ObjectManagerInterface $objectManager) : void
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Checks if the object is in the class list uses inheritance (via instanceof)
     *
     * @param object $object
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function isObjectInClassList(object $object)
    {
        $className = \get_class($object);
        $isObjectInClassListCachedValue = $this->isObjectInClassListCache[$className] ?? null;
        if (null !== $isObjectInClassListCachedValue) {
            return $isObjectInClassListCachedValue;
        }
        foreach ($this->classList as $key => $value) {
            if ($object instanceof $key) {
                $this->isObjectInClassListCache[$className] = true;
                return true;
            }
        }
        $this->isObjectInClassListCache[$className] = false;
        return false;
    }

    /**
     * State reset using reflection (or RESET_STATE_METHOD instead if it exists)
     *
     * @param object $instance
     * @return void
     * @throws \ReflectionException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function resetStateWithReflection(object $instance)
    {
        if (\method_exists($instance, self::RESET_STATE_METHOD)) {
            $instance->{self::RESET_STATE_METHOD}();
            return;
        }
        $className = get_class($instance);
        if (!array_key_exists($className, $this->sortedClassListsByClass)) {
            $temporaryClassList = [];
            foreach ($this->classList as $key => $value) {
                if ($instance instanceof $key) {
                    $temporaryClassList[] = $key;
                }
            }
            $this->sortClasses($temporaryClassList);
            $this->sortedClassListsByClass[$className] = $temporaryClassList;
        }
        foreach ($this->sortedClassListsByClass[$className] as $currentClassName) {
            $this->resetStateWithReflectionByClassName($instance, $currentClassName);
        }
    }

    /**
     * Sorts an array of strings that are class names and interface names
     *
     * Note: This sorting algorithm only takes arrays that are keyed by contiguous numbers starting at zero.
     * Note: This sorting algorithm works with comparators that return false on unrelated items.
     *
     * @param array $array
     * @return void
     */
    private function sortClasses(array &$array) : void
    {
        $i = 0;
        $count = count($array);
        while ($i + 1 < $count) {
            for ($j = $i + 1; $j < $count; $j++) {
                if ($this->sortClassesComparitor($array[$i], $array[$j])) {
                    $swapTemp = $array[$i];
                    $array[$i] = $array[$j];
                    $array[$j] = $swapTemp;
                    continue 2;
                }
            }
            $i++;
        }
    }

    /**
     * Comparator for class/interface sorter that returns true if $b should come before $a.
     *
     * @param string $a
     * @param string $b
     * @return bool
     */
    private function sortClassesComparitor(string $a, string $b) : bool
    {
        if (is_a($a, $b, true)) {
            return true;
        }
        if (is_a($b, $a, true)) {
            return false;
        }
        if (interface_exists($a) && class_exists($b)) {
            return true; // Note: If they aren't related, classes should come before interfaces
        }
        return false; // No relation
    }

    /**
     * State reset using reflection using specific className
     *
     * @param object $instance
     * @param string $className
     * @return void
     */
    private function resetStateWithReflectionByClassName(object $instance, string $className)
    {
        $classResetValues = $this->classList[$className] ?? [];
        $reflectionClass = $this->reflectionCache[$className]
            ?? $this->reflectionCache[$className] = new \ReflectionClass($className);
        foreach ($reflectionClass->getProperties() as $property) {
            $name = $property->getName();
            if (!array_key_exists($name, $classResetValues)) {
                continue;
            }
            $value = $classResetValues[$name];
            $property->setAccessible(true);
            $property->setValue($instance, $value);
            $property->setAccessible(false);
        }
    }
}
