<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\QuoteGraphQl\Model\ErrorMapper;
use Magento\QuoteGraphQl\Model\QuoteException;
use PHPUnit\Framework\TestCase;

class QuoteExceptionTest extends TestCase
{
    /**
     * @param int $errorId
     * @param string $code
     * @return void
     */
    #[DataProvider('quoteExceptionDataProvider')]
    public function testGetExtensions(int $errorId, string $code): void
    {
        $exception = new QuoteException(__('test'), null, $errorId);
        $this->assertEquals($code, $exception->getExtensions()['error_code']);
    }

    /**
     * @return array
     */
    public static function quoteExceptionDataProvider(): array
    {
        $data = [];
        foreach (ErrorMapper::MESSAGE_CODE_IDS as $id => $code) {
            $data[] = [$id, $code];
        }
        $data[] = [777, ErrorMapper::ERROR_UNDEFINED];

        return $data;
    }
}
