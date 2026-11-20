<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestFixture;

interface NativeOnlyInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    // phpcs:ignore Magento2.Annotation.MethodAnnotationStructure.NoCommentBlock
    public function getExtensionAttributes(): NativeOnlyExtensionInterface;
}
