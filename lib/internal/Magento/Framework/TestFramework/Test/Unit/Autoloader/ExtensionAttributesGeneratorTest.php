<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Test\Unit\Autoloader;

use Magento\Framework\TestFramework\Unit\Autoloader\ExtensionAttributesGenerator;

class ExtensionAttributesGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExtensionAttributesGenerator
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new ExtensionAttributesGenerator();
    }

    public function testGenerateExtensionAttributes()
    {
        $this->assertStringMatchesFormat(
            "%Anamespace My;%Aclass SimpleExtension implements \My\SimpleExtensionInterface%A",
            $this->subject->generate('\My\SimpleExtension')
        );
    }

    /**
     * @dataProvider generateNonExtensionAttributesDataProvider
     * @param string $className
     */
    public function testGenerateNonExtensionAttributes($className)
    {
        $this->assertFalse($this->subject->generate($className));
    }

    /**
     * @return array
     */
    public function generateNonExtensionAttributesDataProvider()
    {
        return [
            'non-extension attribute class' => ['\My\SimpleClass'],
            'non-conventional extension attribute name' => ['\My\Extension'],
        ];
    }
}
