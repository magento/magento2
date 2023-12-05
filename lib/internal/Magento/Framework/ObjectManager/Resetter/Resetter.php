<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Resetter;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\ObjectManagerInterface;
use WeakMap;
use WeakReference;

/**
 * Class that keeps track of the instances that need to be reset, and resets them
 */
class Resetter implements ResetterInterface
{

    public const RESET_PATH = '/app/etc/reset.php';

    /** @var WeakMap instances to be reset after request */
    private WeakMap $resetAfterWeakMap;

    /**
     * @var array
     *
     */
    private array $classList = [
        //phpcs:disable Magento2.PHP.LiteralNamespaces
        'Magento\Framework\GraphQl\Query\Fields' => true,
        'Magento\Store\Model\Store' => [
            "_baseUrlCache" => [],
            "_configCache" => null,
            "_configCacheBaseNodes" => [],
            "_dirCache" => [],
            "_urlCache" => []
        ]
    ];

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
    public function __construct()
    {
        if (\file_exists(BP . self::RESET_PATH)) {
            // phpcs:ignore Magento2.Security.IncludeFile.FoundIncludeFile
            $this->classList = array_replace($this->classList, (require BP . '/app/etc/reset.php'));
        }
        $this->resetAfterWeakMap = new WeakMap;
    }

    /**
     * Add instance to be reset later
     *
     * @param object $instance
     * @return void
     */
    public function addInstance(object $instance) : void
    {
        if ($instance instanceof ResetAfterRequestInterface || isset($this->classList[\get_class($instance)])) {
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
        $weakMapSorter = ObjectManager::getInstance()
            ->create(WeakMapSorter::class, ['weakmap' => $this->resetAfterWeakMap]);
        $weakMapSorter->_resetState();
        $temporaryWeakReferenceList = [];
        foreach ($this->resetAfterWeakMap as $weakMapObject => $value) {
            $temporaryWeakReferenceList[] = WeakReference::create($weakMapObject);
            unset($weakMapObject);
            unset($value);
        }
        foreach ($temporaryWeakReferenceList as $weakReference) {
            $instance = $weakReference->get();
            if (!$instance) {
                continue;
            }
            if (!$instance instanceof ResetAfterRequestInterface) {
                $this->resetStateWithReflection($instance);
            }
        }
        unset($temporaryWeakReferenceList);
    }

    /**
     * @inheritDoc
     * @phpcs:disable Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
     */
    public function setObjectManager(ObjectManagerInterface $objectManager) : void
    {
    }

    /**
     * Gets weak map
     *
     * @return WeakMap
     */
    public function getWeakMap() : WeakMap
    {
        throw new \RuntimeException("Not implemented\n");
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
