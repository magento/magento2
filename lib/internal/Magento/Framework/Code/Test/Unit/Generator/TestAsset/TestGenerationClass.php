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
     * @param ParentClass $parentClass
     * @param SourceClass $sourceClass
     * @param \Not_Existing_Class $notExistingClass
     */
    public function __construct(
        ParentClass $parentClass,
        SourceClass $sourceClass,
        \Not_Existing_Class $notExistingClass
    ) {
    }
}
