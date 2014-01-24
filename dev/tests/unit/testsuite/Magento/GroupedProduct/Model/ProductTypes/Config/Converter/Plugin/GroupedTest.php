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

namespace Magento\GroupedProduct\Model\ProductTypes\Config\Converter\Plugin;

class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $config
     * @param array $result
     * @dataProvider afterConvertDataProvider
     */
    public function testAfterConvert($config, $result)
    {
        $model = new \Magento\GroupedProduct\Model\ProductTypes\Config\Converter\Plugin\Grouped();
        $this->assertEquals($result, $model->afterConvert($config));
    }

    /**
     * @return array
     */
    public function afterConvertDataProvider()
    {
        $index = \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE;
        $emptyConfig = array(1, 2, 3);
        $config = array($index => array(1));
        $result = array($index => array(1, 'is_product_set' => true));

        return array(
            'empty config' => array($emptyConfig, $emptyConfig),
            'with grouped' => array($config, $result),
        );
    }
}
