<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Test\Unit\Autoloader;

use Magento\Framework\TestFramework\Unit\Autoloader\ExtensionAttributesInterfaceGenerator;

class ExtensionAttributesInterfaceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExtensionAttributesInterfaceGenerator
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new ExtensionAttributesInterfaceGenerator();
    }

    public function testGenerateExtensionAttributesInterface()
    {
        $this->assertStringMatchesFormat(
            "%Anamespace My;%Ainterface SimpleExtensionInterface extends "
            . "\\Magento\\Framework\\Api\\ExtensionAttributesInterface%A",
            $this->subject->generate('\My\SimpleExtensionInterface')
        );
    }

    /**
     * @dataProvider generateNonExtensionAttributesInterfaceDataProvider
     * @param string $className
     */
    public function testGenerateNonExtensionAttributesInterface($className)
    {
        $this->assertFalse($this->subject->generate($className));
    }

    /**
     * @return array
     */
    public function generateNonExtensionAttributesInterfaceDataProvider()
    {
        return [
            'non-extension attribute interface' => ['\My\SimpleInterface'],
            'non-conventional extension attribute interface name' => ['\My\ExtensionInterface'],
        ];
    }
}
