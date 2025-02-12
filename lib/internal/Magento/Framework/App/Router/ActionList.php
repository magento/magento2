<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Router;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\App\Utility\ReflectionClassFactory;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Module\Dir\Reader as ModuleReader;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Serialize\SerializerInterface;
use ReflectionClass;
use ReflectionException;

/**
 * Class to retrieve action class.
 */
class ActionList
{
    /**
     * Not allowed string in route's action path to avoid disclosing admin url
     */
    public const NOT_ALLOWED_IN_NAMESPACE_PATH = 'adminhtml';

    /**
     * List of application actions
     *
     * @var array
     */
    protected $actions;

    /**
     * @var array
     */
    protected $reservedWords = [
        'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const',
        'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare',
        'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final',
        'finally', 'fn', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'instanceof',
        'insteadof', 'interface', 'isset', 'list', 'match', 'namespace', 'new', 'or', 'print', 'private', 'protected',
        'public', 'require', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'void',
        'while', 'xor', 'yield',
    ];

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $actionInterface;

    /**
     * @var ReflectionClassFactory|null
     */
    private $reflectionClassFactory;

    /**
     * @param CacheInterface $cache
     * @param ModuleReader $moduleReader
     * @param string $actionInterface
     * @param string $cacheKey
     * @param array $reservedWords
     * @param SerializerInterface|null $serializer
     * @param State|null $state
     * @param DirectoryList|null $directoryList
     * @param ReflectionClassFactory|null $reflectionClassFactory
     * @throws FileSystemException
     */
    public function __construct(
        CacheInterface $cache,
        ModuleReader $moduleReader,
        $actionInterface = ActionInterface::class,
        $cacheKey = 'app_action_list',
        $reservedWords = [],
        ?SerializerInterface $serializer = null,
        ?State $state = null,
        ?DirectoryList $directoryList = null,
        ?ReflectionClassFactory $reflectionClassFactory = null
    ) {
        $this->reservedWords = array_merge($reservedWords, $this->reservedWords);
        $this->actionInterface = $actionInterface;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Serialize::class);
        $state = $state ?: ObjectManager::getInstance()->get(State::class);
        $this->reflectionClassFactory = $reflectionClassFactory
            ?: ObjectManager::getInstance()->get(ReflectionClassFactory::class);

        if ($state->getMode() === State::MODE_PRODUCTION) {
            $directoryList = $directoryList ?: ObjectManager::getInstance()->get(DirectoryList::class);
            $file = $directoryList->getPath(DirectoryList::GENERATED_METADATA)
                . '/' . $cacheKey . '.' . 'php';

            if (file_exists($file)) {
                $this->actions = (include $file) ?? $moduleReader->getActionFiles();
            } else {
                $this->actions = $moduleReader->getActionFiles();
            }
        } else {
            $data = $cache->load($cacheKey);
            if (!$data) {
                $this->actions = $moduleReader->getActionFiles();
                $cache->save($this->serializer->serialize($this->actions), $cacheKey);
            } else {
                $this->actions = $this->serializer->unserialize($data);
            }
        }
    }

    /**
     * Retrieve action class
     *
     * @param string $module
     * @param string $area
     * @param string $namespace
     * @param string $action
     * @return null|string
     * @throws ReflectionException
     */
    public function get($module, $area, $namespace, $action)
    {
        if ($area) {
            $area = '\\' . $area;
        }
        $namespace = $namespace !== null ? strtolower($namespace) : '';
        if (strpos($namespace, self::NOT_ALLOWED_IN_NAMESPACE_PATH) !== false) {
            return null;
        }
        if ($action && in_array(strtolower($action), $this->reservedWords)) {
            $action .= 'action';
        }
        $fullPath = str_replace(
            '_',
            '\\',
            strtolower(
                $module . '\\controller' . $area . '\\' . $namespace . '\\' . $action
            )
        );
        try {
            if ($this->validateActionClass($fullPath)) {
                return $this->actions[$fullPath];
            }
        } catch (ReflectionException $e) {
            return null;
        }

        return null;
    }

    /**
     * Validate Action Class
     *
     * @param string $fullPath
     * @return bool
     * @throws ReflectionException
     */
    private function validateActionClass(string $fullPath): bool
    {
        if (isset($this->actions[$fullPath])) {
            if (!is_subclass_of($this->actions[$fullPath], $this->actionInterface)) {
                return false;
            }
            $reflectionClass = $this->reflectionClassFactory->create($this->actions[$fullPath]);
            if ($reflectionClass->isInstantiable()) {
                return true;
            }
        }
        return false;
    }
}
