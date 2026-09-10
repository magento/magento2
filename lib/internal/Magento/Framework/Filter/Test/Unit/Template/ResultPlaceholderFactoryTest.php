<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit\Template;

use Magento\Framework\Filter\Template\ResultPlaceholderFactory;
use Magento\Framework\Filter\Template\SignatureProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResultPlaceholderFactoryTest extends TestCase
{
    private const string SIGNATURE = 'Z0FFbeCU2R8bsVGJuTdkXyiiZBzsaceV';

    /**
     * @var ResultPlaceholderFactory
     */
    private $resultPlaceholderFactory;

    /**
     * @var SignatureProvider|MockObject
     */
    private $signatureProvider;

    protected function setUp(): void
    {
        $this->signatureProvider = $this->createPartialMock(SignatureProvider::class, ['get']);

        $this->signatureProvider->method('get')
            ->willReturn(self::SIGNATURE);

        $this->resultPlaceholderFactory = new ResultPlaceholderFactory($this->signatureProvider);
    }

    public function testCreateBuildsSignedPlaceholderForSlot(): void
    {
        $this->assertSame(self::SIGNATURE . ':slot0:', $this->resultPlaceholderFactory->create(0));
        $this->assertSame(self::SIGNATURE . ':slot12:', $this->resultPlaceholderFactory->create(12));
    }

    public function testCreateBuildsDistinctPlaceholderPerSlot(): void
    {
        $placeholders = [];

        for ($slot = 0; $slot < 20; $slot++) {
            $placeholders[] = $this->resultPlaceholderFactory->create($slot);
        }

        $this->assertSame($placeholders, array_unique($placeholders));
    }

    public function testCreateBuildsPlaceholderThatCannotBePrefixedByAnother(): void
    {
        $this->assertStringEndsWith(':', $this->resultPlaceholderFactory->create(1));
        $this->assertStringStartsNotWith(
            $this->resultPlaceholderFactory->create(1),
            $this->resultPlaceholderFactory->create(11)
        );
    }
}
