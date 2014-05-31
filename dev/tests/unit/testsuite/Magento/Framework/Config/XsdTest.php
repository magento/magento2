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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Config;

class XsdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $xsdFile
     * @param string $invalidXmlFile
     * @param int $expectedErrorsQty
     * @dataProvider invalidXmlFileDataProvider
     */
    public function testInvalidXmlFile($xsdFile, $invalidXmlFile, $expectedErrorsQty)
    {
        $dom = new \DOMDocument();
        $dom->load(__DIR__ . "/_files/{$invalidXmlFile}");
        libxml_use_internal_errors(true);
        $result = $dom->schemaValidate(BP . "/lib/internal/Magento/Framework/Config/etc/{$xsdFile}");

        $errorsQty = count(libxml_get_errors());
        libxml_use_internal_errors(false);

        if ($expectedErrorsQty > 0) {
            $this->assertFalse($result);
        }
        $this->assertEquals($expectedErrorsQty, $errorsQty);
    }

    /**
     * @return array
     */
    public function invalidXmlFileDataProvider()
    {
        return array(array('view.xsd', 'view_invalid.xml', 1), array('theme.xsd', 'theme_invalid.xml', 0));
    }
}
