<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Library;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\App\Utility\AggregateInvoker;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\TestFramework\Integrity\Library\Injectable;
use Magento\TestFramework\Integrity\Library\PhpParser\ParserFactory;
use Magento\TestFramework\Integrity\Library\PhpParser\Tokens;
use Zend\Code\Reflection\FileReflection;

/**
 * Test check if Magento library components contain incorrect dependencies to application layer
 *
 */
class DependencyTest extends \PHPUnit_Framework_TestCase
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

    public function testCheckDependencies()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             */
            function ($file) {
                $componentRegistrar = new ComponentRegistrar();
                $fileReflection = new FileReflection($file);
                $tokens = new Tokens($fileReflection->getContents(), new ParserFactory());
                $tokens->parseContent();

                $dependencies = array_merge(
                    (new Injectable())->getDependencies($fileReflection),
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
                            $this->errors[$fileReflection->getFileName()][] = $dependency;
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

    public function testAppCodeUsage()
    {
        $files = Files::init();
        $componentRegistrar = new ComponentRegistrar();
        $libPaths = $componentRegistrar->getPaths(ComponentRegistrar::LIBRARY);
        $invoker = new AggregateInvoker($this);
        $invoker(
            function ($file) use ($libPaths) {
                $content = file_get_contents($file);
                foreach ($libPaths as $libPath) {
                    if (strpos($file, $libPath) === 0) {
                        $this->assertSame(
                            0,
                            preg_match('~(?<![a-z\\d_:]|->|function\\s)__\\s*\\(~iS', $content),
                            'Function __() is defined outside of the library and must not be used there. ' .
                            'Replacement suggestion: new \\Magento\\Framework\\Phrase()'
                        );
                    }
                }
            },
            $files->getPhpFiles(
                Files::INCLUDE_PUB_CODE |
                Files::INCLUDE_LIBS |
                Files::AS_DATA_SET |
                Files::INCLUDE_NON_CLASSES
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
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
