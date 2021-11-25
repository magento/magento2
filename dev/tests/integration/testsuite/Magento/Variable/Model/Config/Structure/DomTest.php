<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Variable\Model\Config\Structure;

use Magento\TestFramework\Helper\Bootstrap;

class DomTest extends \PHPUnit\Framework\TestCase
{

    public function testMerge()
    {
        $availableVariables = new AvailableVariables(
            [
                'test' => [
                    'test_section/config/allowed' => '1',
                ],
            ],
        );
        $model = Bootstrap::getObjectManager()->create(
            \Magento\Variable\Model\Config\Structure\Dom::class,
            [
                'xml' => '<config/>',
                'availableVariables' => $availableVariables
            ]
        );

        $testConfigXml = $this->getConfigXml();
        $model->merge($testConfigXml);
        $dom = $model->getDom();
        $xml = $dom->saveXML();
        $this->assertStringContainsString('allowed', $xml);
        $this->assertStringNotContainsString('filtered', $xml);
    }

    /**
     * @return string
     */
    private function getConfigXml()
    {
        //phpcs:disable
        $configXml = <<<'XML'
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Config:etc/system_file.xsd">
    <system>
        <section id="test_section" translate="label" type="text" sortOrder="80" showInDefault="1" showInWebsite="1" showInStore="1">
            <label>Test Section</label>
            <tab>catalog</tab>
            <resource>Magento_Catalog::catalog</resource>
            <group id="config" translate="label" type="text" sortOrder="1" showInDefault="1" showInWebsite="1" showInStore="1">
                <label>Test Config</label>
                <field id="allowed" translate="label" type="select" sortOrder="10" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Allowed Option</label>
                </field>
                <field id="filtered" translate="label" type="select" sortOrder="10" showInDefault="1" showInWebsite="1" showInStore="1">
                    <label>Filtered Option</label>
                </field>
            </group>
        </section>
    </system>
</config>
XML;
        //phpcs:enable
        return $configXml;
    }
}
