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
namespace Magento\Cron\Model\Config;

class XsdTest extends \PHPUnit_Framework_TestCase
{
    protected $_xsdFile;

    public function setUp()
    {
        $this->_xsdFile = __DIR__ . "/../../../../../../../../app/code/Magento/Cron/etc/crontab.xsd";
    }

    /**
     * @param string $xmlFile
     * @dataProvider validXmlFileDataProvider
     */
    public function testValidXmlFile($xmlFile)
    {
        $dom = new \DOMDocument();
        $dom->load(__DIR__ . "/_files/{$xmlFile}");
        libxml_use_internal_errors(true);
        $result = $dom->schemaValidate($this->_xsdFile);
        libxml_use_internal_errors(false);
        $this->assertTrue($result);
    }

    /**
     * @return array
     */
    public function validXmlFileDataProvider()
    {
        return array(array('crontab_valid.xml'), array('crontab_valid_without_schedule.xml'));
    }

    /**
     * @param string $xmlFile
     * @param array $expectedErrors
     * @dataProvider invalidXmlFileDataProvider
     */
    public function testInvalidXmlFile($xmlFile, $expectedErrors)
    {
        $dom = new \DOMDocument();
        $dom->load(__DIR__ . "/_files/{$xmlFile}");
        libxml_use_internal_errors(true);
        $dom->schemaValidate($this->_xsdFile);
        $errors = libxml_get_errors();

        $actualErrors = array();
        foreach ($errors as $error) {
            $actualErrors[] = $error->message;
        }

        libxml_use_internal_errors(false);
        $this->assertEquals($expectedErrors, $actualErrors);
    }

    /**
     * @return array
     */
    public function invalidXmlFileDataProvider()
    {
        return array(
            array(
                'crontab_invalid.xml',
                array(
                    "Element 'job', attribute 'wrongName': The attribute 'wrongName' is not allowed.\n",
                    "Element 'job', attribute 'wrongInstance': The attribute 'wrongInstance' is not allowed.\n",
                    "Element 'job', attribute 'wrongMethod': The attribute 'wrongMethod' is not allowed.\n",
                    "Element 'job': The attribute 'name' is required but missing.\n",
                    "Element 'job': The attribute 'instance' is required but missing.\n",
                    "Element 'job': The attribute 'method' is required but missing.\n",
                    "Element 'wrongSchedule': This element is not expected." .
                        " Expected is one of ( schedule, config_path ).\n"
                )
            ),
            array(
                'crontab_invalid_duplicates.xml',
                array(
                    "Element 'job': Duplicate key-sequence ['job1'] in unique identity-constraint 'uniqueJobName'.\n"
                )
            ),
            array(
                'crontab_invalid_without_name.xml',
                array("Element 'job': The attribute 'name' is required but missing.\n")
            ),
            array(
                'crontab_invalid_without_instance.xml',
                array("Element 'job': The attribute 'instance' is required but missing.\n")
            ),
            array(
                'crontab_invalid_without_method.xml',
                array("Element 'job': The attribute 'method' is required but missing.\n")
            )
        );
    }
}
