<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Integration\Fixture;

interface NativeDataInterface
{
    // phpcs:ignore Magento2.Annotation.MethodAnnotationStructure.NoCommentBlock
    public function getId(): int;

    // phpcs:ignore Magento2.Annotation.MethodAnnotationStructure.NoCommentBlock
    public function getName(): ?string;
}
