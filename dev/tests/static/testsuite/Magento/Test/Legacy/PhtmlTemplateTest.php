<?php
/**
 * Backwards-incompatible changes in file system
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Legacy;

use Magento\Framework\Component\ComponentRegistrar;

/**
 * Static test for phtml template files.
 */
class PhtmlTemplateTest extends \PHPUnit\Framework\TestCase
{
    public function testBlockVariableInsteadOfThis()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Test usage of methods and variables in template through $this
             *
             * @param string $file
             */
            function ($file) {
                self::assertDoesNotMatchRegularExpression(
                    '/this->(?!helper)\S*/iS',
                    file_get_contents($file),
                    'Access to members and methods of Block class through $this is ' .
                    'obsolete in phtml templates. Use only $block instead of $this.'
                );
            },
            \Magento\Framework\App\Utility\Files::init()->getPhtmlFiles()
        );
    }

    public function testObsoleteBlockMethods()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Test usage of protected and private methods and variables in template
             *
             * According to naming convention (B5.8, B6.2) all class members
             * in protected or private scope should be prefixed with underscore.
             * Member variables declared "public" should never start with an underscore.
             * Access to protected and private members of Block class is obsolete in phtml templates
             * since introduction of multiple template engines support
             *
             * @param string $file
             */
            function ($file) {
                self::assertDoesNotMatchRegularExpression(
                    '/block->_[^_]+\S*\(/iS',
                    file_get_contents($file),
                    'Access to protected and private members of Block class is ' .
                    'obsolete in phtml templates. Use only public members.'
                );
            },
            \Magento\Framework\App\Utility\Files::init()->getPhtmlFiles()
        );
    }

    public function testObsoleteJavascriptAttributeType()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * "text/javascript" type attribute in not obligatory to use in templates due to HTML5 standards.
             * For more details please go to "http://www.w3.org/TR/html5/scripting-1.html".
             *
             * @param string $file
             */
            function ($file) {
                self::assertDoesNotMatchRegularExpression(
                    '/type="text\/javascript"/',
                    file_get_contents($file),
                    'Please do not use "text/javascript" type attribute.'
                );
            },
            \Magento\Framework\App\Utility\Files::init()->getPhtmlFiles()
        );
    }

    public function testJqueryUiLibraryIsNotUsedInTemplates()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * 'jquery/ui' library is not obligatory to use in phtml files.
             * It's better to use needed jquery ui widget instead.
             *
             * @param string $file
             */
            function ($file) {
                if (strpos($file, '/view/frontend/templates/') !== false
                    || strpos($file, '/view/base/templates/') !== false
                ) {
                    self::assertDoesNotMatchRegularExpression(
                        '/(["\'])jquery\/ui\1/',
                        file_get_contents($file),
                        'Please do not use "jquery/ui" library in templates. Use needed jquery ui widget instead.'
                    );
                }
            },
            \Magento\Framework\App\Utility\Files::init()->getPhtmlFiles()
        );
    }

    public function testJsComponentsAreProperlyInitializedInDataMageInitAttribute()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * JS components in data-mage-init attributes should be initialized not in php.
             * JS components should be initialized in templates for them to be properly statically analyzed for bundling.
             *
             * @param string $file
             */
            function ($file) {
                $whiteList = $this->getWhiteList();
                if (!in_array($file, $whiteList, true)
                    && (strpos($file, '/view/frontend/templates/') !== false
                    || strpos($file, '/view/base/templates/') !== false)
                ) {
                    self::assertDoesNotMatchRegularExpression(
                        '/data-mage-init=(?:\'|")(?!\s*{\s*"[^"]+")/',
                        file_get_contents($file),
                        'Please do not initialize JS component in php. Do it in template.'
                    );
                }
            },
            \Magento\Framework\App\Utility\Files::init()->getPhtmlFiles()
        );
    }

    /**
     * @return array
     */
    private function getWhiteList()
    {
        $whiteListFiles = [];
        $componentRegistrar = new ComponentRegistrar();
        foreach ($this->getFilesData('data_mage_init/whitelist.php') as $fileInfo) {
            $whiteListFiles[] = $componentRegistrar->getPath(ComponentRegistrar::MODULE, $fileInfo[0])
                . DIRECTORY_SEPARATOR . $fileInfo[1];
        }
        return $whiteListFiles;
    }

    /**
     * @param string $filePattern
     * @return array
     */
    private function getFilesData($filePattern)
    {
        $result = [];
        foreach (glob(__DIR__ . '/_files/initialize_javascript/' . $filePattern) as $file) {
            $fileData = include $file;
            $result = array_merge($result, $fileData);
        }
        return $result;
    }

    public function testJsComponentsAreProperlyInitializedInXMagentoInitAttribute()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * JS components in x-magento-init attributes should be initialized not in php.
             * JS components should be initialized in templates for them to be properly statically analyzed for bundling.
             *
             * @param string $file
             */
            function ($file) {
                if (strpos($file, '/view/frontend/templates/') !== false
                    || strpos($file, '/view/base/templates/') !== false
                ) {
                    self::assertNotRegExp(
                        '@x-magento-init.>(?!\s*+{\s*"[^"]+"\s*:\s*{\s*"[\w/-]+")@i',
                        file_get_contents($file),
                        'Please do not initialize JS component in php. Do it in template.'
                    );
                }
            },
            \Magento\Framework\App\Utility\Files::init()->getPhtmlFiles()
        );
    }
}
