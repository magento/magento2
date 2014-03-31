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
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model;

class CategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Category
     */
    protected $model;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Filter\FilterManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filter;

    protected function setUp()
    {
        $this->filter = $this->getMock('Magento\Filter\FilterManager', ['translitUrl'], [], '', false);
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $this->objectManager->getObject('Magento\Catalog\Model\Category', ['filter' => $this->filter]);
    }

    /**
     * @dataProvider getIdentitiesProvider
     * @param array $expected
     * @param array $origData
     * @param array $data
     * @param bool $isDeleted
     */
    public function testGetIdentities($expected, $origData, $data, $isDeleted = false)
    {
        if (is_array($origData)) {
            foreach ($origData as $key => $value) {
                $this->model->setOrigData($key, $value);
            }
        }
        $this->model->setData($data);
        $this->model->isDeleted($isDeleted);
        $this->assertEquals($expected, $this->model->getIdentities());
    }

    /**
     * @return array
     */
    public function getIdentitiesProvider()
    {
        return array(
            array(
                array('catalog_category_1'),
                array('id' => 1, 'name' => 'value'),
                array('id' => 1, 'name' => 'value')
            ),
            array(
                array('catalog_category_1', 'catalog_category_product_1'),
                null,
                array('id' => 1, 'name' => 'value')
            ),
            array(
                array('catalog_category_1', 'catalog_category_product_1'),
                array('id' => 1, 'name' => ''),
                array('id' => 1, 'name' => 'value')
            ),
            array(
                array('catalog_category_1', 'catalog_category_product_1'),
                array('id' => 1, 'name' => 'value'),
                array('id' => 1, 'name' => 'value'),
                true
            ),
        );
    }

    public function testFormatUrlKey()
    {
        $strIn = 'Some string';
        $resultString = 'some';

        $this->filter->expects(
            $this->once()
        )->method(
            'translitUrl'
        )->with(
            $strIn
        )->will(
            $this->returnValue($resultString)
        );

        $this->assertEquals($resultString, $this->model->formatUrlKey($strIn));
    }
}
