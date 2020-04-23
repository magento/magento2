<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Model\Payment\Method\Specification;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Multishipping\Model\Payment\Method\Specification\Enabled;
use Magento\Payment\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Enabled method Test
 */
class EnabledTest extends TestCase
{
    /**
     * Object Manager helper
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Payment config mock
     *
     * @var MockObject|Config
     */
    protected $paymentConfigMock;

    protected function setUp(): void
    {
        $this->paymentConfigMock = $this->createMock(Config::class);
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * Test isSatisfiedBy method
     *
     * @param array $methodsInfo
     * @param bool $result
     * @dataProvider methodsDataProvider
     */
    public function testIsSatisfiedBy($methodsInfo, $result)
    {
        $method = 'method-name';
        $methodsInfo = [$method => $methodsInfo];

        $this->paymentConfigMock->expects(
            $this->once()
        )->method(
            'getMethodsInfo'
        )->willReturn(
            $methodsInfo
        );

        $configSpecification = $this->objectManager->getObject(
            Enabled::class,
            ['paymentConfig' => $this->paymentConfigMock]
        );

        $this->assertEquals(
            $result,
            $configSpecification->isSatisfiedBy($method),
            sprintf('Failed payment method test: "%s"', $method)
        );
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function methodsDataProvider()
    {
        return [
            [['allow_multiple_address' => 1], true],
            [['allow_multiple_address' => 0], false],
            [['no_flag' => 0], false]
        ];
    }
}
