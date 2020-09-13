<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Test\Unit\ExtensionAttribute\Config;

use Magento\Framework\Config\Dom;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\ValidationStateInterface;
use PHPUnit\Framework\TestCase;

class XsdTest extends TestCase
{
    /**
     * @var string
     */
    protected $_schemaFile;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new UrnResolver();
        $this->_schemaFile = $urnResolver->getRealPath('urn:magento:framework:Api/etc/extension_attributes.xsd');
    }

    /**
     * @param string $fixtureXml
     * @param array $expectedErrors
     * @dataProvider exemplarXmlDataProvider
     */
    public function testExemplarXml($fixtureXml, array $expectedErrors)
    {
        $validationStateMock = $this->getMockForAbstractClass(ValidationStateInterface::class);
        $validationStateMock->method('isValidationRequired')
            ->willReturn(true);
        $messageFormat = '%message%';
        $dom = new Dom($fixtureXml, $validationStateMock, [], null, null, $messageFormat);
        $actualResult = $dom->validate($this->_schemaFile, $actualErrors);
        $this->assertEquals($expectedErrors, $actualErrors, "Validation errors does not match.");
        $this->assertEquals(empty($expectedErrors), $actualResult, "Validation result is invalid.");
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function exemplarXmlDataProvider()
    {
        return [
            /** Valid configurations */
            'valid with empty extension attributes' => [
                '<config>
                    <extension_attributes for="Magento\Tax\Api\Data\TaxRateInterface">
                    </extension_attributes>
                </config>',
                [],
            ],
            'valid with one attribute code' => [
                '<config>
                    <extension_attributes for="Magento\Tax\Api\Data\TaxRateInterface">
                        <attribute code="stock_item" type="Magento\CatalogInventory\Api\Data\StockItemInterface" />
                    </extension_attributes>
                </config>',
                [],
            ],
            'valid with multiple attribute codes' => [
                '<config>
                    <extension_attributes for="Magento\Tax\Api\Data\TaxRateInterface">
                        <attribute code="custom_1" type="Magento\Customer\Api\Data\CustomerCustom" />
                        <attribute code="custom_2" type="Magento\CustomerExtra\Api\Data\CustomerCustom2" />
                    </extension_attributes>
                </config>',
                [],
            ],
            'valid with multiple attribute codes with permissions' => [
                '<config>
                    <extension_attributes for="Magento\Tax\Api\Data\TaxRateInterface">
                        <attribute code="custom_1" type="Magento\Customer\Api\Data\CustomerCustom">
                            <resources>
                                <resource ref="Magento_Customer::manage"/>
                            </resources>
                        </attribute>
                        <attribute code="custom_2" type="Magento\CustomerExtra\Api\Data\CustomerCustom2">
                            <resources>
                                <resource ref="Magento_Customer::manage"/>
                                <resource ref="Magento_Catalog::other"/>
                            </resources>
                        </attribute>
                    </extension_attributes>
                </config>',
                [],
            ],
            'valid with attribute code with join' => [
                '<config>
                    <extension_attributes for="Magento\Tax\Api\Data\TaxRateInterface">
                        <attribute code="custom_1" type="Magento\Customer\Api\Data\CustomerCustom">
                            <join reference_table="library_account"
                                  reference_field="customer_id"
                                  join_on_field="id"
                            >
                                <field>library_card_id</field>
                            </join>
                        </attribute>
                    </extension_attributes>
                </config>',
                [],
            ],
            'valid with attribute code with permissions and join' => [
                '<config>
                    <extension_attributes for="Magento\Tax\Api\Data\TaxRateInterface">
                        <attribute code="custom_1" type="Magento\Customer\Api\Data\CustomerCustom">
                            <resources>
                                <resource ref="Magento_Customer::manage"/>
                            </resources>
                            <join reference_table="library_account"
                                  reference_field="customer_id"
                                  join_on_field="id"
                            >
                                <field>library_card_id</field>
                            </join>
                        </attribute>
                    </extension_attributes>
                </config>',
                [],
            ],
            /** Invalid configurations */
            'invalid missing extension_attributes' => [
                '<config/>',
                ["Element 'config': Missing child element(s). Expected is ( extension_attributes )."],
            ],
            'invalid with attribute code with resources without single resource' => [
                '<config>
                    <extension_attributes for="Magento\Tax\Api\Data\TaxRateInterface">
                        <attribute code="custom_1" type="Magento\Customer\Api\Data\CustomerCustom">
                            <resources>
                            </resources>
                        </attribute>
                    </extension_attributes>
                </config>',
                ["Element 'resources': Missing child element(s). Expected is ( resource )."],
            ],
            'invalid with attribute code without join attributes' => [
                '<config>
                    <extension_attributes for="Magento\Tax\Api\Data\TaxRateInterface">
                        <attribute code="custom_1" type="Magento\Customer\Api\Data\CustomerCustom">
                            <join/>
                        </attribute>
                    </extension_attributes>
                </config>',
                [
                    "Element 'join': The attribute 'reference_table' is required but missing.",
                    "Element 'join': The attribute 'join_on_field' is required but missing.",
                    "Element 'join': The attribute 'reference_field' is required but missing.",
                    "Element 'join': Missing child element(s). Expected is ( field ).",
                ],
            ],
        ];
    }
}
