<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\I18n;


class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\I18n\Context
     */
    protected $context;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->context = $objectManagerHelper->getObject('Magento\Tools\I18n\Context');
    }

    /**
     * @param array $context
     * @param string $path
     * @dataProvider dataProviderContextByPath
     */
    public function testGetContextByPath($context, $path)
    {
        $this->assertEquals($context, $this->context->getContextByPath($path));
    }

    /**
     * @return array
     */
    public function dataProviderContextByPath()
    {
        return [
            [[Context::CONTEXT_TYPE_MODULE, 'Magento_Module'], '/app/code/Magento/Module/Block/Test.php'],
            [[Context::CONTEXT_TYPE_THEME, 'area/theme/test.phtml'], '/app/design/area/theme/test.phtml'],
            [[Context::CONTEXT_TYPE_LIB, 'lib/web/module/test.phtml'], '/lib/web/module/test.phtml'],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid path given: "invalid_path".
     */
    public function testGetContextByPathWithInvalidPath()
    {
        $this->context->getContextByPath('invalid_path');
    }

    /**
     * @param string $path
     * @param array $context
     * @dataProvider dataProviderPathToLocaleDirectoryByContext
     */
    public function testBuildPathToLocaleDirectoryByContext($path, $context)
    {
        $this->assertEquals($path, $this->context->buildPathToLocaleDirectoryByContext($context[0], $context[1]));
    }

    /**
     * @return array
     */
    public function dataProviderPathToLocaleDirectoryByContext()
    {
        return [
            ['app/code/Magento/Module/i18n/', [Context::CONTEXT_TYPE_MODULE, 'Magento_Module']],
            ['app/design/theme/test.phtml/i18n/', [Context::CONTEXT_TYPE_THEME, 'theme/test.phtml']],
            ['lib/web/i18n/', [Context::CONTEXT_TYPE_LIB, 'lib/web/module/test.phtml']],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid context given: "invalid_type".
     */
    public function testBuildPathToLocaleDirectoryByContextWithInvalidType()
    {
        $this->context->buildPathToLocaleDirectoryByContext('invalid_type', 'Magento_Module');
    }
}
