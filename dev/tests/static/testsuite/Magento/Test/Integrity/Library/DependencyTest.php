<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Library;

use Laminas\Code\Reflection\ClassReflection;
use Laminas\Code\Reflection\Exception\InvalidArgumentException;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Setup\Module\Di\Code\Reader\FileClassScanner;
use Magento\TestFramework\Integrity\Library\Injectable;
use Magento\TestFramework\Integrity\Library\PhpParser\ParserFactory;
use Magento\TestFramework\Integrity\Library\PhpParser\Tokens;
use PHPUnit\Framework\TestCase;

/**
 * Test check if Magento library components contain incorrect dependencies to application layer
 *
 */
class DependencyTest extends TestCase
{
    /**
     * Collect errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Allowed sub namespaces
     *
     * @return array
     */
    protected function getAllowedNamespaces()
    {
        return [
            'Framework',
            'SomeModule',
            'ModuleName',
            'Setup\Console\CommandList',
            'Setup\Console\CompilerPreparation',
            'Setup\Model\ObjectManagerProvider',
            'Setup\Mvc\Bootstrap\InitParamListener',
            'Store\Model\ScopeInterface',
            'Store\Model\StoreManagerInterface',
            'Directory\Model\CurrencyFactory',
            'PageCache\Model\Cache\Type',
            'Backup\Model\ResourceModel\Db',
            'Backend\Block\Widget\Button',
            'Ui\Component\Container',
            'SalesRule\Model\Rule',
            'SalesRule\Api\Data\RuleInterface',
            'SalesRule\Model\Rule\Interceptor',
            'SalesRule\Model\Rule\Proxy',
            'Theme\Model\View\Design'
        ];
    }

    public function testCheckDependencies(): void
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             */
            function ($file) {
                $this->errors = [];
                $componentRegistrar = new ComponentRegistrar();
                $reflectedFilePath = $this->getFilePath($file);
                $tokens = new Tokens(file_get_contents($reflectedFilePath), new ParserFactory());
                $tokens->parseContent();

                $fileScanner = new FileClassScanner($file);
                $className = $fileScanner->getClassName();
                if ($className) {   // could be not a class but just a php-file
                    $class = new ClassReflection($className);
                    $classUses = (new Injectable())->getDependencies($class);
                } else {
                    $classUses = [];
                }

                $dependencies = array_merge(
                    $classUses,
                    $tokens->getDependencies()
                );
                $allowedNamespaces = str_replace('\\', '\\\\', implode('|', $this->getAllowedNamespaces()));
                $pattern = '#Magento\\\\(?!' . $allowedNamespaces . ').*#';
                foreach ($dependencies as $dependency) {
                    $dependencyPaths = explode('\\', $dependency);
                    $dependencyPaths = array_slice($dependencyPaths, 2);
                    $dependencyPath = implode('\\', $dependencyPaths);
                    $libraryPaths = $componentRegistrar->getPaths(ComponentRegistrar::LIBRARY);
                    foreach ($libraryPaths as $libraryPath) {
                        $filePath = str_replace('\\', '/', $libraryPath .  '/' . $dependencyPath . '.php');
                        if (preg_match($pattern, $dependency) && !file_exists($filePath)) {
                            $this->errors[basename($reflectedFilePath)][] = $dependency;
                        }
                    }
                }

                if (!empty($this->errors)) {
                    $this->fail($this->getFailMessage());
                }
            },
            $this->libraryDataProvider()
        );
    }

    /**
     * copied from laminas-code 3.5.1
     *
     * @param string $filename
     *
     * @return string
     */
    private function getFilePath(string $filename): string
    {
        if (($fileRealPath = realpath($filename)) === false) {
            $fileRealPath = stream_resolve_include_path($filename);
        }

        if (! $fileRealPath) {
            throw new InvalidArgumentException(sprintf(
                'No file for %s was found.',
                $filename
            ));
        }

        return $fileRealPath;
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->errors = [];
    }

    /**
     * Prepare failed message
     *
     * @return string
     */
    protected function getFailMessage()
    {
        $failMessage = '';
        foreach ($this->errors as $class => $dependencies) {
            $failMessage .= $class . ' depends for non-library ' . (count($dependencies) > 1 ? 'classes ' : 'class ');
            foreach ($dependencies as $dependency) {
                $failMessage .= $dependency . ' ';
            }
            $failMessage = trim($failMessage) . PHP_EOL;
        }
        return $failMessage;
    }

    /**
     * Contains all library files
     *
     * @return array
     */
    public function libraryDataProvider()
    {
        // @TODO: remove this code when class Magento\Framework\Data\Collection will fixed
        $componentRegistrar = new ComponentRegistrar();
        include_once $componentRegistrar->getPath(ComponentRegistrar::LIBRARY, 'magento/framework')
            . '/Option/ArrayInterface.php';
        $blackList = Files::init()->readLists(__DIR__ . '/_files/blacklist*.txt');
        $dataProvider = Files::init()->getPhpFiles(Files::INCLUDE_LIBS | Files::AS_DATA_SET);

        foreach ($dataProvider as $key => $data) {
            if (in_array($data[0], $blackList)) {
                unset($dataProvider[$key]);
            } else {
                include_once $data[0];
            }
        }
        return $dataProvider;
    }
}
