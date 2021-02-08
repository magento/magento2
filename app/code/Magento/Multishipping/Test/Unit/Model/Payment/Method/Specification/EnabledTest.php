<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Test\Unit\Model\Payment\Method\Specification;

/**
 * Enabled method Test
 */
class EnabledTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Object Manager helper
     *
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Payment config mock
     *
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Payment\Model\Config
     */
    protected $paymentConfigMock;

    protected function setUp(): void
    {
        $this->paymentConfigMock = $this->createMock(\Magento\Payment\Model\Config::class);
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
            \Magento\Multishipping\Model\Payment\Method\Specification\Enabled::class,
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
