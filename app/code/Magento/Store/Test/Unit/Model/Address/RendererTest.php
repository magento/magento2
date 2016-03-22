<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model\Address;

use Magento\Framework\DataObject;
use Magento\Store\Model\Address\Renderer;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Renderer
     */
    protected $model;

    /**
     * Init mocks for tests
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function setUp()
    {
        $eventManager = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();

        $eventManager->expects($this->once())->method('dispatch')->with('store_address_format');

        $filterManager = $this->getMockBuilder('Magento\Framework\Filter\FilterManager')
            ->disableOriginalConstructor()
            ->setMethods(['template'])
            ->getMock();

        $filterManager->expects($this->once())
            ->method('template')
            ->willReturnCallback(function ($format, $data) {
                return implode("\n", $data['variables']);
            });

        $this->model = new Renderer($eventManager, $filterManager);
    }

    /**
     * @param DataObject $storeInfo
     * @param $type
     * @dataProvider formatDataProvider
     */
    public function testFormat(DataObject $storeInfo, $type)
    {
        $expected = implode("\n", $storeInfo->getData());
        if ($type === 'html') {
            $expected = nl2br($expected);
        }
        $result = $this->model->format($storeInfo, $type);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function formatDataProvider()
    {
        $storeInfo = new DataObject([
            'region' => 'Gondolin',
            'country' => 'Beleriand',
        ]);

        return [
            [$storeInfo, 'html'],
            [$storeInfo, 'text'],
        ];
    }
}
