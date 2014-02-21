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
namespace Magento\Downloadable\Model\Product\TypeTransitionManager\Plugin;

class DownloadableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $invocationChainMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Downloadable\Model\Product\TypeTransitionManager\Plugin\Downloadable
     */
    protected $model;

    protected function setUp()
    {
        $this->requestMock = $this->getMock(
            'Magento\App\Request\Http',
            array(),
            array(),
            '',
            false
        );
        $this->model = new Downloadable($this->requestMock);
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('hasIsVirtual', 'getTypeId', 'setTypeId', '__wakeup'),
            array(),
            '',
            false
        );
        $this->invocationChainMock = $this->getMock('Magento\Code\Plugin\InvocationChain', array(), array(), '', false);
    }

    /**
     * @param string $currentTypeId
     * @dataProvider compatibleTypeDataProvider
     */
    public function testAroundProcessProductWithProductThatCanBeTransformedToDownloadable($currentTypeId)
    {
        $this->requestMock->expects($this->any())->method('getPost')->with('downloadable')
            ->will($this->returnValue('valid_downloadable_data'));
        $this->productMock->expects($this->any())->method('hasIsVirtual')->will($this->returnValue(true));
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue($currentTypeId));
        $this->productMock->expects($this->once())->method('setTypeId')
            ->with(\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE);
        $this->invocationChainMock->expects($this->never())->method('proceed');
        $this->model->aroundProcessProduct(array($this->productMock), $this->invocationChainMock);
    }

    /**
     * @return array
     */
    public function compatibleTypeDataProvider()
    {
        return array(
            array(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE),
            array(\Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL),
            array(\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE),
        );
    }

    /**
     * @param bool $isVirtual
     * @param string $currentTypeId
     * @param string|null $downloadableData
     * @dataProvider productThatCannotBeTransformedToDownloadableDataProvider
     */
    public function testAroundProcessProductWithProductThatCannotBeTransformedToDownloadable(
        $isVirtual,
        $currentTypeId,
        $downloadableData
    ) {
        $this->requestMock->expects($this->any())->method('getPost')->with('downloadable')
            ->will($this->returnValue($downloadableData));
        $this->productMock->expects($this->any())->method('hasIsVirtual')->will($this->returnValue($isVirtual));
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue($currentTypeId));
        $this->productMock->expects($this->never())->method('setTypeId');
        $arguments = array($this->productMock);
        $this->invocationChainMock->expects($this->once())->method('proceed')->with($arguments);
        $this->model->aroundProcessProduct($arguments, $this->invocationChainMock);
    }

    /**
     * @return array
     */
    public function productThatCannotBeTransformedToDownloadableDataProvider()
    {
        return array(
            array(
                true,
                'custom_product_type',
                'valid_downloadable_data',
            ),
            array(
                false,
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                null,
            ),
            array(
                true,
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                null,
            ),
            array(
                false,
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                'valid_downloadable_data',
            ),
        );
    }
}
