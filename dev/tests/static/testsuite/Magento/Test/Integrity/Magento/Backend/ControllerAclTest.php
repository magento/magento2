<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Magento\Backend;

use Magento\TestFramework\Utility\ChangedFiles;
use Magento\Framework\App\Utility\Files;
use Magento\Backend\App\AbstractAction;

class ControllerAclTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Default function for checking accessibility of the ACL resource.
     */
    const ACL_FUNC_NAME = '_isAllowed';

    /**
     * Name of the const. that contains ACL resource path.
     */
    const ACL_CONST_NAME = 'ADMIN_RESOURCE';

    /**
     * Default value from the AbstractResource.
     */
    const DEFAULT_BACKEND_RESOURCE = 'Magento_Backend::admin';

    /**
     * Several backend controllers should be accessible always, and can't be closed by ACL.
     *
     * @var array
     */
    private $whiteListedBackendControllers = [];

    /**
     * List of ACL resources collected from acl.xml files.
     *
     * @var null|array
     */
    private $aclResources;

    /**
     * Set up before test execution.
     */
    protected function setUp(): void
    {
        $whitelistedClasses = [];
        $path = sprintf('%s/_files/controller_acl_test_whitelist_*.txt', __DIR__);
        foreach (glob($path) as $listFile) {
            $whitelistedClasses = array_merge(
                $whitelistedClasses,
                file($listFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
            );
        }
        foreach ($whitelistedClasses as $item) {
            if (substr($item, 0, 1) === '#') {
                continue;
            }
            $this->whiteListedBackendControllers[$item] = 1;
        }
    }

    /**
     * Test ACL in the admin area by various assertions.
     */
    public function testAcl()
    {
        $errorMessages = [];
        $pathMask = sprintf('%s/../../../_files/changed_files*', __DIR__, DIRECTORY_SEPARATOR);
        $changedFiles = ChangedFiles::getPhpFiles($pathMask);
        foreach ($changedFiles as $line) {
            $relativeFilePath = $line[0];
            // we don't have to check tests,
            if ($this->isItTest($relativeFilePath)) {
                continue;
            }

            $controllerPath = $this->getControllerPath($relativeFilePath);
            if (!$controllerPath) {
                continue;
            }

            $controllerClass = $this->getClassByFilePath($controllerPath);
            // skip whitelisted controllers.
            if (isset($this->whiteListedBackendControllers[$controllerClass->getName()])) {
                continue;
            }
            // we don't have to check abstract classes.
            if ($controllerClass->isAbstract()) {
                continue;
            }

            $className = $controllerClass->getName();

            if (!$this->isClassExtendsBackendClass($controllerClass)) {
                $inheritanceMessage = "Backend controller $className have to inherit " . AbstractAction::class;
                $errorMessages[] = $inheritanceMessage;
                continue;
            }

            $isAclRedefinedInTheChildClass = $this->isConstantOverwritten($controllerClass)
                || $this->isMethodOverwritten($controllerClass);
            if (!$isAclRedefinedInTheChildClass) {
                $errorMessages[] = "Backend controller $className have to overwrite _isAllowed method or "
                    . 'ADMIN_RESOURCE constant';
            }

            $errorMessages = array_merge($errorMessages, $this->collectAclErrorsInTheXml($controllerClass));
        }
        sort($errorMessages);
        $this->assertEmpty($errorMessages, implode("\n", $errorMessages));
    }

    /**
     * Collect possible errors for the ACL that exists in the php code but doesn't exists in the XML code.
     *
     * @param \ReflectionClass $class
     * @return array
     */
    private function collectAclErrorsInTheXml(\ReflectionClass $class)
    {
        $errorMessages = [];
        $className = $class->getName();
        $method = $class->getMethod(self::ACL_FUNC_NAME);
        $codeLines = file($method->getFileName());
        $length = $method->getEndLine() - $method->getStartLine();
        $start = $method->getStartLine();
        $codeOfTheMethod = implode(' ', array_slice($codeLines, $start, $length));
        preg_match('~["\']Magento_.*?::.*?["\']~', $codeOfTheMethod, $matches);
        $aclResources = $this->getAclResources();
        foreach ($matches as $resource) {
            $resourceUnquoted = str_replace(['"', "'"], ['', ''], $resource);
            if (!isset($aclResources[$resourceUnquoted])) {
                $errorMessages[] = "ACL $resource exists in $className but doesn't exists in the acl.xml file";
            }
        }
        return $errorMessages;
    }

    /**
     * Collect all available ACL resources from acl.xml files.
     *
     * @return array
     */
    private function getAclResources()
    {
        if ($this->aclResources !== null) {
            return $this->aclResources;
        }
        $aclFiles = Files::init()->getConfigFiles('acl.xml', []);
        $xmlResources = [];
        array_map(function ($file) use (&$xmlResources) {
            $config = simplexml_load_file($file[0]);
            $nodes = $config->xpath('.//resource/@id') ?: [];
            foreach ($nodes as $node) {
                $xmlResources[(string)$node] = $node;
            }
        }, $aclFiles);
        $this->aclResources = $xmlResources;
        return $this->aclResources;
    }

    /**
     * Is ADMIN_RESOURCE constant was overwritten in the child class.
     *
     * @param \ReflectionClass $class
     * @return bool
     */
    private function isConstantOverwritten(\ReflectionClass $class)
    {
        // check that controller overwrites default ACL to some specific
        if ($class->getConstant(self::ACL_CONST_NAME) !== self::DEFAULT_BACKEND_RESOURCE) {
            return true;
        }

        return false;
    }

    /**
     * Is _isAllowed method was overwritten in the child class.
     *
     * @param \ReflectionClass $class
     * @return bool
     */
    private function isMethodOverwritten(\ReflectionClass $class)
    {
        // check that controller overwrites default ACL to some specific (at least we check that it was overwritten).
        $method = $class->getMethod(self::ACL_FUNC_NAME);
        try {
            $method->getPrototype();
            return true;
        } catch (\ReflectionException $e) {
            return false;
        }
    }

    /**
     * Is controller extends Magento\Backend\App\AbstractAction.
     *
     * @param \ReflectionClass $class
     * @return bool
     */
    private function isClassExtendsBackendClass(\ReflectionClass $class)
    {
        while ($parentClass = $class->getParentClass()) {
            if (AbstractAction::class === $parentClass->getName()) {
                return true;
            }
            $class = $parentClass;
        }
        return false;
    }

    /**
     * Check is file looks like a test.
     *
     * @param string $relativeFilePath
     * @return bool
     */
    private function isItTest($relativeFilePath)
    {
        $isTest = (preg_match('~.*?(/dev/tests/|/Test/Unit/).*?\.php$~', $relativeFilePath) === 1);
        return $isTest;
    }

    /**
     * Get c
     *
     * @param string $relativeFilePath
     * @return string
     */
    private function getControllerPath($relativeFilePath)
    {
        if (preg_match('~(Magento\/[^\/]+\/Controller\/Adminhtml\/.*)\.php~', $relativeFilePath, $matches)) {
            if (count($matches) === 2) {
                $partPath = $matches[1];
                return $partPath;
            }
        }
        return '';
    }

    /**
     * Try to get reflection for a admin html controller class by it path.
     *
     * @param string  $controllerPath
     * @return \ReflectionClass
     */
    private function getClassByFilePath($controllerPath)
    {
        $className = str_replace('/', '\\', $controllerPath);
        try {
            $reflectionClass = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            $reflectionClass = new \ReflectionClass(new \stdClass());
        }
        return $reflectionClass;
    }
}
