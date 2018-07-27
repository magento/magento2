<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Model;

class AgreementsValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Model\AgreementsValidator
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new \Magento\Checkout\Model\AgreementsValidator();
    }

    public function testIsValid()
    {
        $this->assertEquals(true, $this->model->isValid());
    }
}
