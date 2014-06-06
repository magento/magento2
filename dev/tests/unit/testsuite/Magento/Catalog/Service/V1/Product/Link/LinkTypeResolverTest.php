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

namespace Magento\Catalog\Service\V1\Product\Link;

class LinkTypeResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LinkTypeResolver
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $providerMock;

    protected function setUp()
    {
        $this->providerMock = $this->getMock('Magento\Catalog\Model\Product\LinkTypeProvider', [], [], '', false);
        $this->model = new LinkTypeResolver($this->providerMock);
    }

    public function testGetTypeIdByCode()
    {
        $linkTypes = ['crosssell' => 1, 'upsell' => 2, 'related' => 4];
        $this->providerMock->expects($this->once())->method('getLinkTypes')->will($this->returnValue($linkTypes));
        $this->assertEquals(4, $this->model->getTypeIdByCode('related'));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Unknown link type code is provided
     */
    public function testGetTypeIdByCodeWithInvalidType()
    {
        $linkTypes = ['crosssell' => 1, 'upsell' => 2, 'related' => 4];
        $this->providerMock->expects($this->once())->method('getLinkTypes')->will($this->returnValue($linkTypes));
        $this->model->getTypeIdByCode('invalid_type');
    }
}
