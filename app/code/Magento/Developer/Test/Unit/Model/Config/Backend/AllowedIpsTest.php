<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\Config\Backend;

use Magento\Developer\Model\Config\Backend\AllowedIps;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class AllowedIpsTest extends TestCase
{
    /**
     * @var AllowedIps
     */
    protected $model;

    protected function setUp(): void
    {
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventMangerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($eventMangerMock);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $escaper = $objectManagerHelper->getObject(Escaper::class);
        $this->model = $objectManagerHelper->getObject(
            AllowedIps::class,
            [
                'context' => $contextMock,
                'escaper' => $escaper,
            ]
        );
    }

    /**
     * @param string $value
     * @param string $expected
     * @dataProvider beforeSaveDataProvider
     * @return void
     */
    public function testBeforeSave($value, $expected)
    {
        $this->assertNull($this->model->getValue());
        $this->model->setValue($value);
        $this->model->beforeSave();
        $this->assertEquals($expected, trim($this->model->getValue()));
    }

    /**
     * @return array
     */
    public static function beforeSaveDataProvider()
    {
        return [
            [ '', '' ],
            [ ', 10.64.206.85, 10. 64.85.206 ', '10.64.206.85' ],
            [ '10.64.206.85, 10.64.1a.x, ,,', '10.64.206.85' ],
            [ ' ,, 10.64.206.85, 10.49.206.85 , ', '10.64.206.85, 10.49.206.85' ],
            [ '2001:db8:0:1234:0:567:8:1, ', '2001:db8:0:1234:0:567:8:1' ], /* valid IPV6 address */
            [ '2001:0cb8:25a3:04c1:1324:8a2b:0471:8221', '2001:0cb8:25a3:04c1:1324:8a2b:0471:8221'],
            [ '255.255.255.255', '255.255.255.255'], /* valid private ip */
            [ '127.0.0.1, ::1', '127.0.0.1, ::1'], /* valid reserved ip */
            ['*[789bo88n=], 12.34.56.78,[,q 049cq9840@@', '12.34.56.78']
        ];
    }
}
