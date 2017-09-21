<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit\Generator\TestAsset;

class TestGenerationClass
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Magento\Framework\Code\Test\Unit\Generator\TestAsset\ParentClass $parentClass
     * @param \Magento\Framework\Code\Test\Unit\Generator\TestAsset\SourceClass $sourceClass
     * @param \Not_Existing_Class $notExistingClass
     */
    public function __construct(
        \Magento\Framework\Code\Test\Unit\Generator\TestAsset\ParentClass $parentClass,
        \Magento\Framework\Code\Test\Unit\Generator\TestAsset\SourceClass $sourceClass,
        \Not_Existing_Class $notExistingClass
    ) {
    }
}
