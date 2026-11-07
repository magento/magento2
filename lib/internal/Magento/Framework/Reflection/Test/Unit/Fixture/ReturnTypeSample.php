<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit\Fixture;

use Magento\Framework\Reflection\Test\Unit\Fixture\UseClasses\SampleOne;

class ReturnTypeSample
{
    /**
     * @return ReturnTypeSampleProperty
     */
    public function getReturnTypeSampleProperty()
    {
        return new ReturnTypeSampleProperty();
    }

    /**
     * @return SampleOne
     */
    public function getImportedSampleOne()
    {
        return new SampleOne();
    }
}
