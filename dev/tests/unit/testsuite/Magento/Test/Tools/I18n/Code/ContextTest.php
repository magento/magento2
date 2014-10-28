<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Tools\I18n\Code;

use Magento\Tools\I18n\Code\Context;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\I18n\Code\Context
     */
    protected $context;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->context = $objectManagerHelper->getObject('Magento\Tools\I18n\Code\Context');
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
        return array(
            array(array(Context::CONTEXT_TYPE_MODULE, 'Magento_Module'), '/app/code/Magento/Module/Block/Test.php'),
            array(array(Context::CONTEXT_TYPE_THEME, 'area/theme/test.phtml'), '/app/design/area/theme/test.phtml'),
            array(array(Context::CONTEXT_TYPE_LIB, 'lib/web/module/test.phtml'), '/lib/web/module/test.phtml'),
        );
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
        return array(
            array('app/code/Magento/Module/i18n/', array(Context::CONTEXT_TYPE_MODULE, 'Magento_Module')),
            array('app/design/theme/test.phtml/i18n/', array(Context::CONTEXT_TYPE_THEME, 'theme/test.phtml')),
            array('lib/web/i18n/', array(Context::CONTEXT_TYPE_LIB, 'lib/web/module/test.phtml')),
        );
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
