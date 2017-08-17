<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Language;

use \Magento\Framework\App\Language\Config;

/**
 * Test for configuration of language
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolver;

    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolverMock;

    /** @var Config */
    protected $config;

    protected function setUp()
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->urnResolverMock = $this->createMock(\Magento\Framework\Config\Dom\UrnResolver::class);
        $this->urnResolverMock->expects($this->any())
            ->method('getRealPath')
            ->with('urn:magento:framework:App/Language/package.xsd')
            ->willReturn($this->urnResolver->getRealPath('urn:magento:framework:App/Language/package.xsd'));
        $validationStateMock = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $validationStateMock->method('isValidationRequired')
            ->willReturn(true);
        $domFactoryMock = $this->createMock(\Magento\Framework\Config\DomFactory::class);
        $domFactoryMock->expects($this->once())
            ->method('createDom')
            ->willReturnCallback(
                function ($arguments) use ($validationStateMock) {
                    return new \Magento\Framework\Config\Dom(
                        $arguments['xml'],
                        $validationStateMock,
                        [],
                        null,
                        $arguments['schemaFile']
                    );
                }
            );
        $this->config = new Config(
            file_get_contents(__DIR__ . '/_files/language.xml'),
            $this->urnResolverMock,
            $domFactoryMock
        );
    }

    public function testConfiguration()
    {
        $this->assertEquals('en_GB', $this->config->getCode());
        $this->assertEquals('Magento', $this->config->getVendor());
        $this->assertEquals('en_GB', $this->config->getPackage());
        $this->assertEquals('100', $this->config->getSortOrder());
        $this->assertEquals(
            [
                ['vendor' => 'oxford-university', 'package' => 'en_us'],
                ['vendor' => 'oxford-university', 'package' => 'en_gb'],
            ],
            $this->config->getUses()
        );
    }

    public function testGetSchemaFile()
    {
        $method = new \ReflectionMethod($this->config, 'getSchemaFile');
        $method->setAccessible(true);
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:App/Language/package.xsd'),
            $method->invoke($this->config)
        );
    }
}
