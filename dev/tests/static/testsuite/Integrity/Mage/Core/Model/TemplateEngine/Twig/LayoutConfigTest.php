<?php
/**
 * Validates that all layouts with service_calls actually reference a valid service call
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Integrity_Mage_Core_DataService_Model_LayoutConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array string[] $_serviceCalls Array of valid service calls available to layouts
     */
    protected static $_serviceCalls;

    /**
     * Gathers all valid service calls from config files
     */
    public static function setUpBeforeClass()
    {
        /**
         * @var array string[] $configFiles
         */
        $configFiles = Utility_Files::init()->getConfigFiles('service_calls.xml', array());
        /**
         * @var string $file
         */
        foreach ($configFiles as $file) {
            /**
             * @var DOMDocument $dom
             */
            $dom = new DOMDocument();
            $dom->loadXML(file_get_contents($file[0]));

            /**
             * @var DOMNodeList $serviceCalls
             */
            $serviceCalls = $dom->getElementsByTagName('service_calls');
            $serviceCalls = $serviceCalls->item(0);
            if ($serviceCalls != null && $serviceCalls->hasChildNodes()) {

                /**
                 * @var $serviceCall DOMNode
                 */
                foreach ($serviceCalls->childNodes as $serviceCall) {
                    if ($serviceCall->localName == 'service_call') {
                        self::$_serviceCalls[] = $serviceCall->attributes->getNamedItem('name')->nodeValue;
                    }
                }
            }
        }
    }

    /**
     * Given a layout file, test whetehr all of its service calls are valid
     *
     * @param $layoutFile
     * @param bool $dummy Describes whether a dummy array was passed in, indicating that no files were found
     *
     * @dataProvider xmlDataProvider
     */
    public function testXmlFile($layoutFile, $dummy = false)
    {
        if (!$dummy) {
            /**
             * @var DOMDocument $dom
             */
            $dom = new DOMDocument();
            $dom->loadXML(file_get_contents($layoutFile));
            $this->assertNotNull($dom);

            /**
             * @var DOMNodeList $dataList
             */
            $dataList = $dom->getElementsByTagName('data');

            /**
             * @var DOMNode $data
             */
            foreach ($dataList as $data) {
                if ($data->hasAttributes()) {
                    /** @var DOMNode $serviceCallAttribute */
                    $serviceCallAttribute = $data->attributes->getNamedItem('service_call');
                    if ($serviceCallAttribute) {

                        /**
                         * @var string $serviceCall
                         */
                        $serviceCall = $serviceCallAttribute->nodeValue;
                        $this->assertTrue(
                            in_array($serviceCall, self::$_serviceCalls),
                            "Unknown service call: $serviceCall"
                        );
                    }
                }
            }
        }
    }

    /**
     * Provides a list of layout files to test, or a dummy array if no files are found
     *
     * @return array
     */
    public function xmlDataProvider()
    {
        $files = Utility_Files::init()->getLayoutFiles();
        if (empty($files)) {
            $files = array(
                array('dummy', true)
            );
        }

        return $files;
    }
}