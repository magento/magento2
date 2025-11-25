<?php
/**
 *  Copyright © Magento, Inc. All rights reserved.
 *  See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\Test\Unit\Autoloader;

use Magento\Framework\TestFramework\Unit\Autoloader\ExtensionAttributesInterfaceGenerator;
use PHPUnit\Framework\TestCase;

class ExtensionAttributesInterfaceGeneratorTest extends TestCase
{
    /**
     * @var ExtensionAttributesInterfaceGenerator
     */
    private $subject;

    protected function setUp(): void
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
    public static function generateNonExtensionAttributesInterfaceDataProvider()
    {
        return [
            'non-extension attribute interface' => ['\My\SimpleInterface'],
            'non-conventional extension attribute interface name' => ['\My\ExtensionInterface'],
        ];
    }
}
