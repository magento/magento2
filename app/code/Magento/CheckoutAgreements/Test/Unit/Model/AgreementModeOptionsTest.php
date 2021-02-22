<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Model\AgreementModeOptions;

class AgreementModeOptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CheckoutAgreements\Model\AgreementModeOptions
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(\Magento\CheckoutAgreements\Model\AgreementModeOptions::class);
    }

    public function testGetOptionsArray()
    {
        $expected = [
            AgreementModeOptions::MODE_AUTO => __('Automatically'),
            AgreementModeOptions::MODE_MANUAL => __('Manually')
        ];
        $this->assertEquals($expected, $this->model->getOptionsArray());
    }
}
