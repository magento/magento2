<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Utility;

/**
 * Search for children classes in list of files.
 */
class ChildrenClassesSearch
{
    /**
     * @var ClassNameExtractor
     */
    private $classNameExtractor;

    /**
     * ChildrenClassesSearch constructor.
     */
    public function __construct()
    {
        $this->classNameExtractor = new ClassNameExtractor();
    }

    /**
     * Get list of classes name which are subclasses of mentioned class.
     *
     * @param array $fileList
     * @param string $parent
     * @param bool $asDataSet
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getClassesWhichAreChildrenOf(array $fileList, string $parent, bool $asDataSet = true): array
    {
        $found = [];

        foreach ($fileList as $file) {
            $name = $asDataSet ? $file[0] : $file;
            $class = $this->classNameExtractor->getNameWithNamespace(file_get_contents($name));

            if ($class) {
                $classReflection = new \ReflectionClass($class);
                if ($classReflection->isSubclassOf($parent)) {
                    $found[] = $class;
                }
            }
        }

        return $found;
    }
}
