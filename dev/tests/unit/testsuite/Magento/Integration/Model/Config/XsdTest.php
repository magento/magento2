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
namespace Magento\Integration\Model\Config;

/**
 * Test for validation rules implemented by XSD schema for integration configuration.
 */
class XsdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_schemaFile;

    protected function setUp()
    {
        $this->_schemaFile = BP . '/app/code/Magento/Integration/etc/integration/config.xsd';
    }

    /**
     * @param string $fixtureXml
     * @param array $expectedErrors
     * @dataProvider exemplarXmlDataProvider
     */
    public function testExemplarXml($fixtureXml, array $expectedErrors)
    {
        $messageFormat = '%message%';
        $dom = new \Magento\Framework\Config\Dom($fixtureXml, array(), null, null, $messageFormat);
        $actualResult = $dom->validate($this->_schemaFile, $actualErrors);
        $this->assertEquals(empty($expectedErrors), $actualResult, "Validation result is invalid.");
        $this->assertEquals($expectedErrors, $actualErrors, "Validation errors does not match.");
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function exemplarXmlDataProvider()
    {
        return array(
            /** Valid configurations */
            'valid' => array(
                '<integrations>
                    <integration name="TestIntegration">
                        <email>test-integration@magento.com</email>
                        <endpoint_url>https://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                array()
            ),
            'valid with several entities' => array(
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                    <integration name="TestIntegration2">
                        <email>test-integration2@magento.com</email>
                    </integration>
                </integrations>',
                array()
            ),
            /** Missing required elements */
            'empty root node' => array(
                '<integrations/>',
                array("Element 'integrations': Missing child element(s). Expected is ( integration ).")
            ),
            'empty integration' => array(
                '<integrations>
                    <integration name="TestIntegration" />
                </integrations>',
                array("Element 'integration': Missing child element(s). Expected is ( email ).")
            ),
            'integration without email' => array(
                '<integrations>
                    <integration name="TestIntegration1">
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                array("Element 'endpoint_url': This element is not expected. Expected is ( email ).")
            ),
            /** Empty nodes */
            'empty email' => array(
                '<integrations>
                    <integration name="TestIntegration1">
                        <email></email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                array(
                    "Element 'email': [facet 'pattern'] The value '' is not " .
                    "accepted by the pattern '[^@]+@[^\.]+\..+'.",
                    "Element 'email': '' is not a valid value of the atomic type 'emailType'."
                )
            ),
            'endpoint_url is empty' => array(
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url></endpoint_url>
                    </integration>
                </integrations>',
                array(
                    "Element 'endpoint_url': [facet 'minLength'] The value has a length of '0'; this underruns" .
                    " the allowed minimum length of '4'.",
                    "Element 'endpoint_url': '' is not a valid value of the atomic type 'urlType'."
                )
            ),
            'identity_link_url is empty' => array(
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url></identity_link_url>
                    </integration>
                </integrations>',
                array(
                    "Element 'identity_link_url': [facet 'minLength'] The value has a length of '0'; this underruns" .
                    " the allowed minimum length of '4'.",
                    "Element 'identity_link_url': '' is not a valid value of the atomic type 'urlType'."
                )
            ),
            /** Invalid structure */
            'irrelevant root node' => array(
                '<integration name="TestIntegration"/>',
                array("Element 'integration': No matching global declaration available for the validation root.")
            ),
            'irrelevant node in root' => array(
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                    <invalid/>
                </integrations>',
                array("Element 'invalid': This element is not expected. Expected is ( integration ).")
            ),
            'irrelevant node in integration' => array(
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <invalid/>
                    </integration>
                </integrations>',
                array("Element 'invalid': This element is not expected.")
            ),
            'irrelevant node in authentication' => array(
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                        <invalid/>
                    </integration>
                </integrations>',
                array("Element 'invalid': This element is not expected.")
            ),
            /** Excessive attributes */
            'invalid attribute in root' => array(
                '<integrations invalid="invalid">
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                array("Element 'integrations', attribute 'invalid': The attribute 'invalid' is not allowed.")
            ),
            'invalid attribute in integration' => array(
                '<integrations>
                    <integration name="TestIntegration1" invalid="invalid">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                array("Element 'integration', attribute 'invalid': The attribute 'invalid' is not allowed.")
            ),
            'invalid attribute in email' => array(
                '<integrations>
                    <integration name="TestIntegration1">
                        <email invalid="invalid">test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                array("Element 'email', attribute 'invalid': The attribute 'invalid' is not allowed.")
            ),
            'invalid attribute in endpoint_url' => array(
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url invalid="invalid">http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                array("Element 'endpoint_url', attribute 'invalid': The attribute 'invalid' is not allowed.")
            ),
            'invalid attribute in identity_link_url' => array(
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url invalid="invalid">http://endpoint.url</identity_link_url>
                    </integration>
                </integrations>',
                array("Element 'identity_link_url', attribute 'invalid': The attribute 'invalid' is not allowed.")
            ),
            /** Missing or empty required attributes */
            'integration without name' => array(
                '<integrations>
                    <integration>
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                array("Element 'integration': The attribute 'name' is required but missing.")
            ),
            'integration with empty name' => array(
                '<integrations>
                    <integration name="">
                        <email>test-integration1@magento.com</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                array(
                    "Element 'integration', attribute 'name': [facet 'minLength'] The value '' has a length of '0'; " .
                    "this underruns the allowed minimum length of '2'.",
                    "Element 'integration', attribute 'name': " .
                    "'' is not a valid value of the atomic type 'integrationNameType'."
                )
            ),
            /** Invalid values */
            'invalid email' => array(
                '<integrations>
                    <integration name="TestIntegration1">
                        <email>invalid</email>
                        <endpoint_url>http://endpoint.url</endpoint_url>
                        <identity_link_url>http://www.example.com/identity</identity_link_url>
                    </integration>
                </integrations>',
                array(
                    "Element 'email': [facet 'pattern'] The value 'invalid' " .
                    "is not accepted by the pattern '[^@]+@[^\.]+\..+'.",
                    "Element 'email': 'invalid' is not a valid value of the atomic type 'emailType'."
                )
            )
        );
    }
}
