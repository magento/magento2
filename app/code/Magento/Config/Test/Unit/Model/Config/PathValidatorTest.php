<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Test\Unit\Model\Config;

use Magento\Config\Model\Config\PathValidator;
use Magento\Config\Model\Config\Structure;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Test class for PathValidator.
 *
 * @see PathValidator
 */
class PathValidatorTest extends \PHPUnit\Framework\TestCase
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
    protected function setUp()
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

    /**
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @expectedExceptionMessage The "test/test/test" path doesn't exist. Verify and try again.
     */
    public function testValidateWithException()
    {
        $this->structureMock->expects($this->once())
            ->method('getFieldPaths')
            ->willReturn([]);

        $this->assertTrue($this->model->validate('test/test/test'));
    }
}
