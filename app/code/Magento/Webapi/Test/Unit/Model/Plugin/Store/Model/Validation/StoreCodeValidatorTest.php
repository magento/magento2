<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model\Plugin\Store\Model\Validation;

use Magento\Store\Model\Validation\StoreCodeValidator as Subject;
use Magento\Webapi\Model\Plugin\Store\Model\Validation\StoreCodeValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreCodeValidatorTest extends TestCase
{
    /**
     * @var Subject|MockObject
     */
    private $subjectMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->subjectMock = $this->getMockBuilder(Subject::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return void
     */
    public function testAfterIsValidStore(): void
    {
        $storeCodeValidator = new StoreCodeValidator();
        $this->assertFalse($storeCodeValidator->afterIsValid($this->subjectMock, true, 'rest'));
    }
}
