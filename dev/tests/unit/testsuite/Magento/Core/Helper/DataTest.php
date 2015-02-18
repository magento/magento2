<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testJsonEncode()
    {
        $valueToEncode = 'valueToEncode';
        $translateInlineMock = $this->getMockBuilder('Magento\Framework\Translate\InlineInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $translateInlineMock->expects($this->once())
            ->method('processResponseBody');
        $context = $this->objectManager->getObject(
            'Magento\Framework\App\Helper\Context',
            [
                'translateInline' => $translateInlineMock,
            ]
        );
        $helper = $this->getHelper(
            [
                'context' => $context,
            ]
        );

        $this->assertEquals('"valueToEncode"', $helper->jsonEncode($valueToEncode));
    }

    public function testJsonDecode()
    {
        $helper = $this->getHelper([]);
        $this->assertEquals('"valueToDecode"', $helper->jsonEncode('valueToDecode'));
    }

    /**
     * Get helper instance
     *
     * @param array $arguments
     * @return Data
     */
    private function getHelper($arguments)
    {
        return $this->objectManager->getObject('Magento\Core\Helper\Data', $arguments);
    }
}
