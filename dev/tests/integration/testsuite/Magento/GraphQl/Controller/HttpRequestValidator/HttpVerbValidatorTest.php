<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller\HttpRequestValidator;

use Magento\Framework\App\HttpRequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test HttpVerbValidator
 */
class HttpVerbValidatorTest extends TestCase
{
    /**
     * @var HttpVerbValidator
     */
    private $httpVerbValidator;

    /**
     * @var HttpRequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @inheritDoc
     */
    protected function setup(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->requestMock = $this->getMockBuilder(HttpRequestInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'isPost',
                ]
            )->addMethods(
                [
                    'getParam',
                ]
            )
            ->getMockForAbstractClass();

        $this->httpVerbValidator = $objectManager->get(HttpVerbValidator::class);
    }

    /**
     * Test for validate method
     *
     * @param string $query
     * @param bool $needException
     * @dataProvider validateDataProvider
     */
    public function testValidate(string $query, bool $needException): void
    {
        $this->requestMock
            ->expects($this->once())
            ->method('isPost')
            ->willReturn(false);

        $this->requestMock
            ->method('getParam')
            ->with('query', '')
            ->willReturn($query);

        if ($needException) {
            $this->expectExceptionMessage('Syntax Error: Unexpected <EOF>');
        }

        $this->httpVerbValidator->validate($this->requestMock);
    }

    /**
     * @return array
     */
    public static function validateDataProvider(): array
    {
        return [
            [
                'query' => '',
                'needException' => false,
            ],
            [
                'query' => ' ',
                'needException' => true
            ],
        ];
    }
}
