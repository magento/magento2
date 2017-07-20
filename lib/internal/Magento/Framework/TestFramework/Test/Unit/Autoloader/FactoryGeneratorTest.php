<?php
/**
 *  Copyright © Magento, Inc. All rights reserved.
 *  See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Test\Unit\Autoloader;

use Magento\Framework\TestFramework\Unit\Autoloader\FactoryGenerator;

class FactoryGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FactoryGenerator
     */
    private $subject;

    protected function setUp()
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
    public function generateNonFactoryDataProvider()
    {
        return [
            'non-factory class' => ['\My\SimpleClass'],
            'non-conventional factory name' => ['\My\Factory'],
        ];
    }
}
