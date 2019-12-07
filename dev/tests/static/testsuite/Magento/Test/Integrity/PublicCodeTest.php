<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity;

use Magento\Framework\App\Utility\Files;

/**
 * Tests @api annotated code integrity
 */
class PublicCodeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * List of simple return types that are used in docblocks.
     * Used to check if type declared in a docblock of a method is a class or interface
     *
     * @var array
     */
    private $simpleReturnTypes = [
        '$this', 'void', 'string', 'int', 'bool', 'boolean', 'integer', 'null'
    ];

    /**
     * @var string[]|null
     */
    private $blockWhitelist;

    /**
     * Return whitelist class names
     *
     * @return string[]
     */
    private function getWhitelist(): array
    {
        if ($this->blockWhitelist === null) {
            $whiteListFiles = str_replace(
                '\\',
                '/',
                realpath(__DIR__) . '/_files/whitelist/public_code*.txt'
            );
            $whiteListItems = [];
            foreach (glob($whiteListFiles) as $fileName) {
                $whiteListItems = array_merge(
                    $whiteListItems,
                    file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
                );
            }
            $this->blockWhitelist = $whiteListItems;
        }
        return $this->blockWhitelist;
    }

    /**
     * Since blocks can be referenced from templates, they should be stable not to break theme customizations.
     * So all blocks should be @api annotated. This test checks that all blocks declared in layout files are public
     *
     * @param $layoutFile
     * @throws \ReflectionException
     * @dataProvider layoutFilesDataProvider
     */
    public function testAllBlocksReferencedInLayoutArePublic($layoutFile)
    {
        $nonPublishedBlocks = [];
        $xml = simplexml_load_file($layoutFile);
        $elements = $xml->xpath('//block | //referenceBlock') ?: [];
        /** @var $node \SimpleXMLElement */
        foreach ($elements as $node) {
            $class = (string) $node['class'];
            if ($class && \class_exists($class) && !in_array($class, $this->getWhitelist())) {
                $reflection = (new \ReflectionClass($class));
                if (strpos($reflection->getDocComment(), '@api') === false) {
                    $nonPublishedBlocks[] = $class;
                }
            }
        }
        if (count($nonPublishedBlocks)) {
            $this->fail(
                "Layout file '$layoutFile' uses following blocks that are not marked with @api annotation:\n"
                . implode(",\n", array_unique($nonPublishedBlocks))
            );
        }
    }

    /**
     * Find all layout update files in magento modules and themes.
     *
     * @return array
     * @throws \Exception
     */
    public function layoutFilesDataProvider()
    {
        return Files::init()->getLayoutFiles([], true);
    }

    /**
     * We want to avoid situation when a type is marked public (@api annotated) but one of its methods
     * returns or accepts the value of non-public type.
     * This test walks through all public PHP types and makes sure that all their method arguments
     * and return values are public types.
     *
     * @param string $class
     * @throws \ReflectionException
     * @dataProvider publicPHPTypesDataProvider
     */
    public function testAllPHPClassesReferencedFromPublicClassesArePublic($class)
    {
        $nonPublishedClasses = [];
        $reflection = new \ReflectionClass($class);
        $filter = \ReflectionMethod::IS_PUBLIC;
        if ($reflection->isAbstract()) {
            $filter = $filter | \ReflectionMethod::IS_PROTECTED;
        }
        $methods = $reflection->getMethods($filter);
        foreach ($methods as $method) {
            if ($method->isConstructor()) {
                continue;
            }
            $nonPublishedClasses = $this->checkParameters($class, $method, $nonPublishedClasses);
            /* Taking into account docblock return types since this code
             is written on early php 7 when return types are not actively used */
            $returnTypes = [];
            if ($method->hasReturnType()) {
                if (!$method->getReturnType()->isBuiltin()) {
                    $returnTypes = [trim($method->getReturnType()->__toString(), '?[]')];
                }
            } else {
                $returnTypes = $this->getReturnTypesFromDocComment($method->getDocComment());
            }
            $nonPublishedClasses = $this->checkReturnValues($class, $returnTypes, $nonPublishedClasses);
        }

        if (count($nonPublishedClasses)) {
            $this->fail(
                "Public type '" . $class . "' references following non-public types:\n"
                . implode("\n", array_unique($nonPublishedClasses))
            );
        }
    }

    /**
     * Retrieve list of all interfaces and classes in Magento codebase that are marked with @api annotation.
     * @return array
     * @throws \Exception
     */
    public function publicPHPTypesDataProvider()
    {
        $files = Files::init()->getPhpFiles(Files::INCLUDE_LIBS | Files::INCLUDE_APP_CODE);
        $result = [];
        foreach ($files as $file) {
            $fileContents = \file_get_contents($file);
            if (strpos($fileContents, '@api') !== false) {
                foreach ($this->getDeclaredClassesAndInterfaces($file) as $class) {
                    if (!in_array($class->getName(), $this->getWhitelist())
                        && (class_exists($class->getName()) || interface_exists($class->getName()))
                    ) {
                        $result[$class->getName()] = [$class->getName()];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Retrieve list of classes and interfaces declared in the file
     *
     * @param string $file
     * @return \Zend\Code\Scanner\ClassScanner[]
     */
    private function getDeclaredClassesAndInterfaces($file)
    {
        $fileScanner = new \Magento\Setup\Module\Di\Code\Reader\FileScanner($file);
        return $fileScanner->getClasses();
    }

    /**
     * Check if a class is @api annotated
     *
     * @param \ReflectionClass $class
     * @return bool
     */
    private function isPublished(\ReflectionClass $class)
    {
        return strpos($class->getDocComment(), '@api') !== false;
    }

    /**
     * Simplified check of class relation.
     *
     * @param string $classNameA
     * @param string $classNameB
     * @return bool
     */
    private function areClassesFromSameVendor($classNameA, $classNameB)
    {
        $classNameA = ltrim($classNameA, '\\');
        $classNameB = ltrim($classNameB, '\\');
        $aVendor = substr($classNameA, 0, strpos($classNameA, '\\'));
        $bVendor = substr($classNameB, 0, strpos($classNameB, '\\'));
        return $aVendor === $bVendor;
    }

    /**
     * Check if the class belongs to the list of classes generated by Magento on demand.
     *
     * We don't need to check @api annotation coverage for generated classes
     *
     * @param string $className
     * @return bool
     */
    private function isGenerated($className)
    {
        return substr($className, -18) === 'ExtensionInterface' || substr($className, -7) === 'Factory';
    }

    /**
     * Retrieves list of method return types from method doc comment
     *
     * Introduced this method to abstract complexity of coping with types in "return" annotation
     *
     * @param string $docComment
     * @return array
     */
    private function getReturnTypesFromDocComment($docComment)
    {
        // TODO: add docblock namespace resolving using third-party library
        if (preg_match('/@return (\S*)/', $docComment, $matches)) {
            return array_map(
                'trim',
                explode('|', $matches[1])
            );
        } else {
            return [];
        }
    }

    /**
     * Check method return values
     *
     * TODO: improve return type filtration
     *
     * @param string $class
     * @param array $returnTypes
     * @param array $nonPublishedClasses
     * @return mixed
     */
    private function checkReturnValues($class, array $returnTypes, array $nonPublishedClasses)
    {
        foreach ($returnTypes as $returnType) {
            if (!in_array($returnType, $this->simpleReturnTypes)
                && !$this->isGenerated($returnType)
                && \class_exists($returnType)
            ) {
                $returnTypeReflection = new \ReflectionClass($returnType);
                if (!$returnTypeReflection->isInternal()
                    && $this->areClassesFromSameVendor($returnType, $class)
                    && !$this->isPublished($returnTypeReflection)
                ) {
                    $nonPublishedClasses[$returnType] = $returnType;
                }
            }
        }
        return $nonPublishedClasses;
    }

    /**
     * Check if all method parameters are public
     * @param string $class
     * @param \ReflectionMethod $method
     * @param array $nonPublishedClasses
     * @return array
     */
    private function checkParameters($class, \ReflectionMethod $method, array $nonPublishedClasses)
    {
        /* Ignoring docblocks for argument types */
        foreach ($method->getParameters() as $parameter) {
            if ($parameter->hasType()
                && !$parameter->getType()->isBuiltin()
                && !$this->isGenerated($parameter->getType()->__toString())
            ) {
                $parameterClass = $parameter->getClass();
                /*
                 * We don't want to check integrity of @api coverage of classes
                 * that belong to different vendors, because it is too complicated.
                 * Example:
                 *  If Magento class references non-@api annotated class from Zend,
                 *  we don't want to fail test, because Zend is considered public by default,
                 *  and we don't care if Zend classes are @api-annotated
                 */
                if (!$parameterClass->isInternal()
                    && $this->areClassesFromSameVendor($parameterClass->getName(), $class)
                    && !$this->isPublished($parameterClass)
                ) {
                    $nonPublishedClasses[$parameterClass->getName()] = $parameterClass->getName();
                }
            }
        }
        return $nonPublishedClasses;
    }
}
