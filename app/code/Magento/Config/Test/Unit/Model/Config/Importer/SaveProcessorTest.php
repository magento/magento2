<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Importer;

use Magento\Config\Model\Config\Backend\Currency\AbstractCurrency;
use Magento\Config\Model\Config\Backend\Currency\Base;
use Magento\Config\Model\Config\Importer\SaveProcessor;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Stdlib\ArrayUtils;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Test for SaveProcessor.
 *
 * @see Importer
 */
class SaveProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SaveProcessor
     */
    private $model;

    /**
     * @var ArrayUtils|Mock
     */
    private $arrayUtilsMock;

    /**
     * @var PreparedValueFactory|Mock
     */
    private $valueFactoryMock;

    /**
     * @var ScopeConfigInterface|Mock
     */
    private $scopeConfigMock;

    /**
     * @var Value|Mock
     */
    private $valueMock;

    /**
     * @var AbstractCurrency|Mock
     */
    private $currencyValueMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->arrayUtilsMock = $this->getMockBuilder(ArrayUtils::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->valueFactoryMock = $this->getMockBuilder(PreparedValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->valueMock = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->currencyValueMock = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->setMethods(['getScope', 'getScopeId', 'beforeSave', 'afterSave', 'addData'])
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->model = new SaveProcessor(
            $this->arrayUtilsMock,
            $this->valueFactoryMock,
            $this->scopeConfigMock
        );
    }

    public function testProcess()
    {
        $data = [
            'default' => [
                'web' => ['unsecure' => ['base_url' => 'http://magento2.local/']],
                'currency' => ['options' => ['base' => 'EUR']]
            ],
            'websites' => ['base' => ['web' => ['unsecure' => ['base_url' => 'http://magento3.local/']]]],
        ];

        $this->valueMock->expects($this->exactly(2))
            ->method('beforeSave');
        $this->valueMock->expects($this->exactly(2))
            ->method('afterSave');

        $value1 = clone $this->valueMock;
        $value2 = clone $this->valueMock;

        $this->currencyValueMock->expects($this->once())
            ->method('getScope')
            ->willReturn('default');
        $this->currencyValueMock->expects($this->once())
            ->method('getScopeId')
            ->willReturn(null);
        $this->currencyValueMock->expects($this->once())
            ->method('addData')
            ->with(['groups' => ['options' => ['fields' => ['allow' => ['value' => ['EUR', 'USD']]]]]]);
        $this->arrayUtilsMock->expects($this->exactly(2))
            ->method('flatten')
            ->willReturnMap([
                [
                    [
                        'web' => ['unsecure' => ['base_url' => 'http://magento2.local/']],
                        'currency' => ['options' => ['base' => 'EUR']]
                    ],
                    '',
                    '/',
                    [
                        'web/unsecure/base_url' => 'http://magento2.local/',
                        'currency/options/base' => 'EUR'
                    ]
                ],
                [
                    ['web' => ['unsecure' => ['base_url' => 'http://magento3.local/']]],
                    '',
                    '/',
                    ['web/unsecure/base_url' => 'http://magento3.local/']
                ]
            ]);
        $this->scopeConfigMock->expects($this->exactly(4))
            ->method('getValue')
            ->willReturnMap([
                ['web/unsecure/base_url', 'default', null, 'http://magento2.local/'],
                ['currency/options/base', 'default', null, 'EUR'],
                ['currency/options/allow', 'default', null, 'EUR,USD'],
                ['web/unsecure/base_url', 'websites', 'base', 'http://magento3.local/']
            ]);
        $this->valueFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->willReturnMap([
                ['web/unsecure/base_url', 'http://magento2.local/', 'default', null, $value1],
                ['currency/options/base', 'EUR', 'default', null, $this->currencyValueMock],
                ['web/unsecure/base_url', 'http://magento3.local/', 'websites', 'base', $value2]
            ]);

        $this->assertSame(null, $this->model->process($data));
    }
}
