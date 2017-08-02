<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Router;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Module\Dir\Reader as ModuleReader;

/**
 * Class \Magento\Framework\App\Router\ActionList
 *
 * @since 2.0.0
 */
class ActionList
{
    /**
     * Not allowed string in route's action path to avoid disclosing admin url
     */
    const NOT_ALLOWED_IN_NAMESPACE_PATH = 'adminhtml';

    /**
     * List of application actions
     *
     * @var array
     * @since 2.0.0
     */
    protected $actions;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $reservedWords = [
        'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const',
        'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare',
        'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final',
        'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'instanceof',
        'insteadof','interface', 'isset', 'list', 'namespace', 'new', 'or', 'print', 'private', 'protected',
        'public', 'require', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var',
        'while', 'xor', 'void',
    ];

    /**
     * @var SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @var string
     * @since 2.2.0
     */
    private $actionInterface;

    /**
     * ActionList constructor
     *
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param ModuleReader $moduleReader
     * @param string $actionInterface
     * @param string $cacheKey
     * @param array $reservedWords
     * @param SerializerInterface|null $serializer
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Config\CacheInterface $cache,
        ModuleReader $moduleReader,
        $actionInterface = \Magento\Framework\App\ActionInterface::class,
        $cacheKey = 'app_action_list',
        $reservedWords = [],
        SerializerInterface $serializer = null
    ) {
        $this->reservedWords = array_merge($reservedWords, $this->reservedWords);
        $this->actionInterface = $actionInterface;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Serialize::class);
        $data = $cache->load($cacheKey);
        if (!$data) {
            $this->actions = $moduleReader->getActionFiles();
            $cache->save($this->serializer->serialize($this->actions), $cacheKey);
        } else {
            $this->actions = $this->serializer->unserialize($data);
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
     * @since 2.0.0
     */
    public function get($module, $area, $namespace, $action)
    {
        if ($area) {
            $area = '\\' . $area;
        }
        if (strpos($namespace, self::NOT_ALLOWED_IN_NAMESPACE_PATH) !== false) {
            return null;
        }
        if (in_array(strtolower($action), $this->reservedWords)) {
            $action .= 'action';
        }
        $fullPath = str_replace(
            '_',
            '\\',
            strtolower(
                $module . '\\controller' . $area . '\\' . $namespace . '\\' . $action
            )
        );
        if (isset($this->actions[$fullPath])) {
            return is_subclass_of($this->actions[$fullPath], $this->actionInterface) ? $this->actions[$fullPath] : null;
        }
        return null;
    }
}
