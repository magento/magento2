<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Checkout\Model\AgreementsValidator;
use PHPUnit\Framework\TestCase;

class AgreementsValidatorTest extends TestCase
{
    /**
     * @var AgreementsValidator
     */
    protected $model;

    protected function setUp(): void
    {
        $this->model = new AgreementsValidator();
    }

    public function testIsValid()
    {
        $this->assertTrue($this->model->isValid());
    }
}
