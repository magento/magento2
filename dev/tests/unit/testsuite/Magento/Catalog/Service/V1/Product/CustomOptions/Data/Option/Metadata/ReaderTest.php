<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata;

use Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata\Reader
     */
    protected $reader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $defaultReaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectReaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionMock;

    protected function setUp()
    {
        $this->defaultReaderMock =
            $this->getMock('Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata\ReaderInterface');
        $this->selectReaderMock =
            $this->getMock('Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata\ReaderInterface');
        $this->optionMock =
            $this->getMock('Magento\Catalog\Model\Product\Option', ['getType', '__wakeup'], [], '', false);
        $this->reader = new \Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata\Reader(
            [
                'default' => $this->defaultReaderMock,
                'select' => $this->selectReaderMock
            ]
        );
    }

    public function testReadOptionWithTypeText()
    {
        $this->optionMock->expects($this->once())->method('getType')->will($this->returnValue('text'));
        $this->defaultReaderMock->expects($this->once())->method('read')->with($this->optionMock);
        $this->reader->read($this->optionMock);
    }

    public function testReadOptionWithTypeSelect()
    {
        $this->optionMock->expects($this->once())->method('getType')->will($this->returnValue('select'));
        $this->selectReaderMock->expects($this->once())->method('read')->with($this->optionMock);
        $this->reader->read($this->optionMock);
    }
}
