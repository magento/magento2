<?php
/**
 *  Copyright © Magento, Inc. All rights reserved.
 *  See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\Test\Unit\Autoloader;

use Magento\Framework\TestFramework\Unit\Autoloader\FactoryGenerator;
use PHPUnit\Framework\TestCase;

class FactoryGeneratorTest extends TestCase
{
    /**
     * @var FactoryGenerator
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new FactoryGenerator();
    }

    public function testGenerateFactory()
    {
        $this->assertStringMatchesFormat(
            '%Anamespace My%Aclass SimpleFactory%Afunction create%A',
            $this->subject->generate('\My\SimpleFactory')
        );
    }

    /**
     * @dataProvider generateNonFactoryDataProvider
     * @param string $className
     */
    public function testGenerateNonFactory($className)
    {
        $this->assertFalse($this->subject->generate($className));
    }

    /**
     * @return array
     */
    public static function generateNonFactoryDataProvider()
    {
        return [
            'non-factory class' => ['\My\SimpleClass'],
            'non-conventional factory name' => ['\My\Factory'],
        ];
    }
}
