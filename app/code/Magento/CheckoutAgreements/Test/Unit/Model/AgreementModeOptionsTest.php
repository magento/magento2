<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Model\AgreementModeOptions;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class AgreementModeOptionsTest extends TestCase
{
    /**
     * @var AgreementModeOptions
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(AgreementModeOptions::class);
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
