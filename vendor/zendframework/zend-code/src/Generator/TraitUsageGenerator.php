<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Code\Generator;

use Reflection;
use ReflectionMethod;

class TraitUsageGenerator extends AbstractGenerator
{
    /**
     * @var ClassGenerator
     */
    protected $classGenerator;

    /**
     * @var array Array of trait names
     */
    protected $traits = array();

    /**
     * @var array Array of trait aliases
     */
    protected $traitAliases = array();

    /**
     * @var array Array of trait overrides
     */
    protected $traitOverrides = array();

    /**
     * @var array Array of string names
     */
    protected $uses = array();

    public function __construct(ClassGenerator $classGenerator)
    {
        $this->classGenerator = $classGenerator;
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function addUse($use, $useAlias = null)
    {
        if (! empty($useAlias)) {
            $use .= ' as ' . $useAlias;
        }

        $this->uses[$use] = $use;
        return $this;
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function getUses()
    {
        return array_values($this->uses);
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function addTrait($trait)
    {
        $traitName = $trait;
        if (is_array($trait)) {
            if (! array_key_exists('traitName', $trait)) {
                throw new Exception\InvalidArgumentException('Missing required value for traitName');
            }
            $traitName = $trait['traitName'];

            if (array_key_exists('aliases', $trait)) {
                foreach ($trait['aliases'] as $alias) {
                    $this->addAlias($alias);
                }
            }

            if (array_key_exists('insteadof', $trait)) {
                foreach ($trait['insteadof'] as $insteadof) {
                    $this->addTraitOverride($insteadof);
                }
            }
        }

        if (! $this->hasTrait($traitName)) {
            $this->traits[] = $traitName;
        }

        return $this;
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function addTraits(array $traits)
    {
        foreach ($traits as $trait) {
            $this->addTrait($trait);
        }

        return $this;
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function hasTrait($traitName)
    {
        return in_array($traitName, $this->traits);
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function getTraits()
    {
        return $this->traits;
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function removeTrait($traitName)
    {
        $key = array_search($traitName, $this->traits);
        if (false !== $key) {
            unset($this->traits[$key]);
        }

        return $this;
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function addTraitAlias($method, $alias, $visibility = null)
    {
        $traitAndMethod = $method;
        if (is_array($method)) {
            if (! array_key_exists('traitName', $method)) {
                throw new Exception\InvalidArgumentException('Missing required argument "traitName" for $method');
            }

            if (! array_key_exists('method', $method)) {
                throw new Exception\InvalidArgumentException('Missing required argument "method" for $method');
            }

            $traitAndMethod = $method['traitName'] . '::' . $method['method'];
        }

        // Validations
        if (false === strpos($traitAndMethod, "::")) {
            throw new Exception\InvalidArgumentException(
                'Invalid Format: $method must be in the format of trait::method'
            );
        }
        if (! is_string($alias)) {
            throw new Exception\InvalidArgumentException('Invalid Alias: $alias must be a string or array.');
        }
        if ($this->classGenerator->hasMethod($alias)) {
            throw new Exception\InvalidArgumentException('Invalid Alias: Method name already exists on this class.');
        }
        if (null !== $visibility
            && $visibility !== ReflectionMethod::IS_PUBLIC
            && $visibility !== ReflectionMethod::IS_PRIVATE
            && $visibility !== ReflectionMethod::IS_PROTECTED
        ) {
            throw new Exception\InvalidArgumentException(
                'Invalid Type: $visibility must of ReflectionMethod::IS_PUBLIC,'
                . ' ReflectionMethod::IS_PRIVATE or ReflectionMethod::IS_PROTECTED'
            );
        }

        list($trait, $method) = explode('::', $traitAndMethod);
        if (! $this->hasTrait($trait)) {
            throw new Exception\InvalidArgumentException('Invalid trait: Trait does not exists on this class');
        }

        $this->traitAliases[$traitAndMethod] = array(
            'alias'      => $alias,
            'visibility' => $visibility
        );

        return $this;
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function getTraitAliases()
    {
        return $this->traitAliases;
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function addTraitOverride($method, $traitsToReplace)
    {
        if (false === is_array($traitsToReplace)) {
            $traitsToReplace = array($traitsToReplace);
        }

        $traitAndMethod = $method;
        if (is_array($method)) {
            if (! array_key_exists('traitName', $method)) {
                throw new Exception\InvalidArgumentException('Missing required argument "traitName" for $method');
            }

            if (! array_key_exists('method', $method)) {
                throw new Exception\InvalidArgumentException('Missing required argument "method" for $method');
            }

            $traitAndMethod = (string) $method['traitName'] . '::' . (string) $method['method'];
        }

        // Validations
        if (false === strpos($traitAndMethod, "::")) {
            throw new Exception\InvalidArgumentException(
                'Invalid Format: $method must be in the format of trait::method'
            );
        }

        list($trait, $method) = explode("::", $traitAndMethod);
        if (! $this->hasTrait($trait)) {
            throw new Exception\InvalidArgumentException('Invalid trait: Trait does not exists on this class');
        }

        if (! array_key_exists($traitAndMethod, $this->traitOverrides)) {
            $this->traitOverrides[$traitAndMethod] = array();
        }

        foreach ($traitsToReplace as $traitToReplace) {
            if (! is_string($traitToReplace)) {
                throw new Exception\InvalidArgumentException(
                    'Invalid Argument: $traitToReplace must be a string or array of strings'
                );
            }

            if (! in_array($traitToReplace, $this->traitOverrides[$traitAndMethod])) {
                $this->traitOverrides[$traitAndMethod][] = $traitToReplace;
            }
        }

        return $this;
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function removeTraitOverride($method, $overridesToRemove = null)
    {
        if (! array_key_exists($method, $this->traitOverrides)) {
            return $this;
        }

        if (null === $overridesToRemove) {
            unset($this->traitOverrides[$method]);
            return $this;
        }

        $overridesToRemove = (! is_array($overridesToRemove))
            ? array($overridesToRemove)
            : $overridesToRemove;
        foreach ($overridesToRemove as $traitToRemove) {
            $key = array_search($traitToRemove, $this->traitOverrides[$method]);
            if (false !== $key) {
                unset($this->traitOverrides[$method][$key]);
            }
        }
        return $this;
    }

    /**
     * @inherit Zend\Code\Generator\TraitUsageInterface
     */
    public function getTraitOverrides()
    {
        return $this->traitOverrides;
    }

    /**
     * @inherit Zend\Code\Generator\GeneratorInterface
     */
    public function generate()
    {
        $output = '';
        $indent = $this->getIndentation();
        $traits = $this->getTraits();

        if (empty($traits)) {
            return $output;
        }

        $output .= $indent . 'use ' . implode(', ', $traits);

        $aliases   = $this->getTraitAliases();
        $overrides = $this->getTraitOverrides();
        if (empty($aliases) && empty($overrides)) {
            $output .= ";" . self::LINE_FEED . self::LINE_FEED;
            return $output;
        }

        $output .= ' {' . self::LINE_FEED;
        foreach ($aliases as $method => $alias) {
            $visibility = (null !== $alias['visibility'])
                ? current(Reflection::getModifierNames($alias['visibility'])) . ' '
                : '';

            // validation check
            if ($this->classGenerator->hasMethod($alias['alias'])) {
                throw new Exception\RuntimeException(sprintf(
                    'Generation Error: Aliased method %s already exists on this class',
                    $alias['alias']
                ));
            }

            $output .=
                $indent
                . $indent
                . $method
                . ' as '
                . $visibility
                . $alias['alias']
                . ';'
                . self::LINE_FEED;
        }

        foreach ($overrides as $method => $insteadofTraits) {
            foreach ($insteadofTraits as $insteadofTrait) {
                $output .=
                    $indent
                    . $indent
                    . $method
                    . ' insteadof '
                    . $insteadofTrait
                    . ';'
                    . self::LINE_FEED;
            }
        }

        $output .= self::LINE_FEED . $indent . '}' . self::LINE_FEED . self::LINE_FEED;

        return $output;
    }
}
