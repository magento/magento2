<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config;

use Magento\Config\Model\Config\PathValidator;
use Magento\Config\Model\Config\Structure;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

/**
 * Test class for PathValidator.
 *
 * @see PathValidator
 */
class PathValidatorTest extends TestCase
{
    /**
     * @var PathValidator
     */
    private $model;

    /**
     * @var Structure|Mock
     */
    private $structureMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->structureMock = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new PathValidator(
            $this->structureMock
        );
    }

    public function testValidate()
    {
        $this->structureMock->expects($this->once())
            ->method('getFieldPaths')
            ->willReturn([
                'test/test/test' => [
                    'test/test/test'
                ]
            ]);

        $this->assertTrue($this->model->validate('test/test/test'));
    }

    public function testValidateWithException()
    {
        $this->expectException('Magento\Framework\Exception\ValidatorException');
        $this->expectExceptionMessage('The "test/test/test" path doesn\'t exist. Verify and try again.');
        $this->structureMock->expects($this->once())
            ->method('getFieldPaths')
            ->willReturn([]);

        $this->assertTrue($this->model->validate('test/test/test'));
    }
}
