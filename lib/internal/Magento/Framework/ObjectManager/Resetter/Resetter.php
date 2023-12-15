<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Resetter;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
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


    /**
     * @var array
     */
    private array $reflectionCache = [];

    /**
     * Constructor
     *
     * @return void
     * @phpcs:disable Magento2.Functions.DiscouragedFunction
     */
    public function __construct(
        private ComponentRegistrarInterface $componentRegistrar,
        private array $classList = [],
    ) {
        foreach ($this->getPaths() as $resetPath) {
            if (!\file_exists($resetPath)) {
                continue;
            }
            $resetData = \json_decode(\file_get_contents($resetPath), true);
            $this->classList = array_replace($this->classList, $resetData);
        }
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
            || isset($this->classList[\get_class($instance)])
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
     * State reset without reflection
     *
     * @param object $instance
     * @return void
     * @throws \ReflectionException
     */
    private function resetStateWithReflection(object $instance)
    {
        if (\method_exists($instance, self::RESET_STATE_METHOD)) {
            $instance->{self::RESET_STATE_METHOD}();
            return;
        }
        $className = \get_class($instance);
        $reflectionClass = $this->reflectionCache[$className]
            ?? $this->reflectionCache[$className] = new \ReflectionClass($className);
        foreach ($reflectionClass->getProperties() as $property) {
            $type = $property->getType()?->getName();
            if (empty($type) && preg_match('/@var\s+([^\s]+)/', $property->getDocComment(), $matches)) {
                $type = $matches[1];
                if (\str_contains($type, '[]')) {
                    $type = 'array';
                }
            }
            $name = $property->getName();
            if (!in_array($type, ['bool', 'array', 'null', 'true', 'false'], true)) {
                continue;
            }
            $value = $this->classList[$className][$name] ??
            match ($type) {
                'bool' => false,
                'true' => true,
                'false' => false,
                'array' => [],
                'null' => null,
            };
            $property->setAccessible(true);
            $property->setValue($instance, $value);
            $property->setAccessible(false);
        }
    }
}
