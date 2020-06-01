<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
