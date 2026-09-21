<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
        $this->structureMock = $this->createMock(Structure::class);

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

    /**
     * Test partial path validation - should match paths starting with the partial path
     *
     * @return void
     */
    public function testValidatePartialPath()
    {
        $partialPath = 'web/secure';
        $fullPaths = [
            'web/secure/base_url' => ['web/secure/base_url'],
            'web/secure/use_in_frontend' => ['web/secure/use_in_frontend'],
            'web/unsecure/base_url' => ['web/unsecure/base_url'],
        ];

        $this->structureMock->expects($this->once())
            ->method('getElementByConfigPath')
            ->with($partialPath)
            ->willReturn(null);

        $this->structureMock->expects($this->once())
            ->method('getFieldPaths')
            ->willReturn($fullPaths);

        $this->assertTrue($this->model->validate($partialPath));
    }

    /**
     * Test partial path validation with no matches - should throw exception
     *
     * @return void
     */
    public function testValidatePartialPathWithNoMatches()
    {
        $partialPath = 'web/does_not_exist';
        $fullPaths = [
            'web/secure/base_url' => ['web/secure/base_url'],
            'web/secure/use_in_frontend' => ['web/secure/use_in_frontend'],
            'web/unsecure/base_url' => ['web/unsecure/base_url'],
        ];

        $this->expectException('Magento\Framework\Exception\ValidatorException');
        $this->expectExceptionMessage('The "web/does_not_exist" path doesn\'t exist. Verify and try again.');

        $this->structureMock->expects($this->once())
            ->method('getElementByConfigPath')
            ->with($partialPath)
            ->willReturn(null);

        $this->structureMock->expects($this->once())
            ->method('getFieldPaths')
            ->willReturn($fullPaths);

        $this->model->validate($partialPath);
    }

    /**
     * Test exact path match takes precedence over partial match
     *
     * @return void
     */
    public function testValidateExactPathMatch()
    {
        $exactPath = 'web/secure';
        $fullPaths = [
            'web/secure' => ['web/secure'],
            'web/secure/base_url' => ['web/secure/base_url'],
            'web/unsecure/base_url' => ['web/unsecure/base_url'],
        ];

        $this->structureMock->expects($this->once())
            ->method('getElementByConfigPath')
            ->with($exactPath)
            ->willReturn(null);

        $this->structureMock->expects($this->once())
            ->method('getFieldPaths')
            ->willReturn($fullPaths);

        $this->assertTrue($this->model->validate($exactPath));
    }
}
