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
 * @package     Magento_Usa
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Usa\Model\Shipping\Carrier\Dhl;

class InternationalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

    public function setUp()
    {
        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testPrepareShippingLabelContent()
    {
        $xml = simplexml_load_file(
            __DIR__ . DIRECTORY_SEPARATOR . '_files'
            . DIRECTORY_SEPARATOR . 'response_shipping_label.xml'
        );
        $result = $this->_invokePrepareShippingLabelContent($xml);
        $this->assertEquals(1111, $result->getTrackingNumber());
        $this->assertEquals(base64_decode('OutputImageContent'), $result->getShippingLabelContent());
    }

    /**
     * @dataProvider prepareShippingLabelContentExceptionDataProvider
     * @expectedException \Magento\Core\Exception
     * @expectedExceptionMessage Unable to retrieve shipping label
     */
    public function testPrepareShippingLabelContentException(\SimpleXMLElement $xml)
    {
        $this->_invokePrepareShippingLabelContent($xml);
    }

    /**
     * @return array
     */
    public function prepareShippingLabelContentExceptionDataProvider()
    {
        $filesPath = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR;
        $empty = $billingNumberOnly = $outputImageOnly = simplexml_load_file(
            $filesPath . 'response_shipping_label.xml'
        );
        unset(
            $empty->AirwayBillNumber, $empty->LabelImage,
            $billingNumberOnly->LabelImage, $outputImageOnly->AirwayBillNumber
        );

        return array(
            array($empty),
            array($billingNumberOnly),
            array($outputImageOnly),
        );
    }

    /**
     * @param \SimpleXMLElement $xml
     * @return \Magento\Object
     */
    protected function _invokePrepareShippingLabelContent(\SimpleXMLElement $xml)
    {
        $model = $this->_helper->getObject('Magento\Usa\Model\Shipping\Carrier\Dhl\International');
        $method = new \ReflectionMethod($model, '_prepareShippingLabelContent');
        $method->setAccessible(true);
        return $method->invoke($model, $xml);
    }
}
