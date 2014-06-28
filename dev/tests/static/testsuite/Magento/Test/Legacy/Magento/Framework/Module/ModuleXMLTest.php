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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Test\Legacy\Magento\Framework\Module;

/**
 * Test for obsolete nodes/attributes in the module.xml
 */
class ModuleXMLTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $file
     * @dataProvider moduleXmlDataProvider
     */
    public function testModuleXml($file)
    {
        $xml = simplexml_load_file($file);
        $this->assertEmpty(
            $xml->xpath('/config/module/@version'),
            'The "version" attribute is obsolete. Use "schema_version" instead.'
        );
    }

    /**
     * @return array
     */
    public function moduleXmlDataProvider()
    {
        return \Magento\TestFramework\Utility\Files::init()->getConfigFiles('module.xml');
    }
}
