<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto\Test\Unit\Code\Generator;

use Magento\Framework\Code\Generator\ClassGenerator;
use Magento\Framework\Dto\Code\GetMutatorSourceCode;
use Magento\Framework\Dto\Test\Unit\Code\Generator\Mock\SampleDto;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class DtoMutatorTest extends TestCase
{
    /**
     * @var GetMutatorSourceCode
     */
    private $getMutatorSourceCode;

    /**
     * Prepare test env
     */
    protected function setUp()
    {
        $this->getMutatorSourceCode = (new ObjectManager($this))->getObject(
            GetMutatorSourceCode::class,
            [
                'classGenerator' => new ClassGenerator()
            ]
        );
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testShouldGenerateDtoMutator(): void
    {
        $generatedMutator = $this->getMutatorSourceCode->execute(
            SampleDto::class,
            SampleDto::class . 'Mutator'
        );

        $generatedMutator = preg_replace('/^\s+$/m', '', $generatedMutator);

        $this->assertStringEqualsFile(__DIR__ . '/_files/Mutator.txt', $generatedMutator);
    }
}
