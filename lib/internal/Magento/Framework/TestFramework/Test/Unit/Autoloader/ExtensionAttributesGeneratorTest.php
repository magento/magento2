<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\Test\Unit\Autoloader;

use Magento\Framework\TestFramework\Unit\Autoloader\ExtensionAttributesGenerator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ExtensionAttributesGeneratorTest extends TestCase
{
    /**
     * @var ExtensionAttributesGenerator
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ExtensionAttributesGenerator();
    }

    public function testGenerateExtensionAttributes()
    {
        $this->assertStringMatchesFormat(
            "%Anamespace My;%Aclass SimpleExtension implements SimpleExtensionInterface%A",
            $this->subject->generate('\My\SimpleExtension')
        );
    }

    /**     * @param string $className
     */
    #[DataProvider('generateNonExtensionAttributesDataProvider')]
    public function testGenerateNonExtensionAttributes($className)
    {
        $this->assertFalse($this->subject->generate($className));
    }

    /**
     * @return array
     */
    public static function generateNonExtensionAttributesDataProvider()
    {
        return [
            'non-extension attribute class' => ['\My\SimpleClass'],
            'non-conventional extension attribute name' => ['\My\Extension'],
        ];
    }
}
