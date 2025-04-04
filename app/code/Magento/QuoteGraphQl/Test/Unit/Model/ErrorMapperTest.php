<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model;

use Magento\QuoteGraphQl\Model\ErrorMapper;
use PHPUnit\Framework\TestCase;

class ErrorMapperTest extends TestCase
{
    /**
     * @var ErrorMapper
     */
    private ErrorMapper $errorMapper;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->errorMapper = new ErrorMapper();
        parent::setUp();
    }

    /**
     * @dataProvider dataProviderForTestGetErrorMessageId
     * @param $message
     * @param $expectedId
     * @return void
     */
    public function testGetErrorMessageId($message, $expectedId): void
    {
        $this->assertEquals($expectedId, $this->errorMapper->getErrorMessageId($message));
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetErrorMessageId(): array
    {
        $data = [];
        foreach (ErrorMapper::MESSAGE_IDS as $code => $id) {
            $data[] = [$code, $id];
        }
        $data[] = ['Some random message', ErrorMapper::ERROR_UNDEFINED_ID];
        return $data;
    }
}
