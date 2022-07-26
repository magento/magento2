<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config;

use Magento\Config\Model\Config\PathValidator;
use Magento\Config\App\Config\Source\RuntimeConfigSource;
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
     * @var RuntimeConfigSource|Mock
     */
    private $configMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(RuntimeConfigSource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new PathValidator(
            $this->configMock
        );
    }

    public function testValidate()
    {
        $this->configMock->expects($this->once())
            ->method('get')
            ->willReturn('test');

        $this->assertTrue($this->model->validate('test/test/test'));
    }

    public function testValidateWithException()
    {
        $this->expectException('Magento\Framework\Exception\ValidatorException');
        $this->expectExceptionMessage('The "test/test/test" path doesn\'t exist. Verify and try again.');
        $this->configMock->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->assertTrue($this->model->validate('test/test/test'));
    }
}
