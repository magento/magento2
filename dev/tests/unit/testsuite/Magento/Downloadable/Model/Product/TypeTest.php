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
 * @package     Magento_Downloadable
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Downloadable\Model\Product;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Downloadable\Model\Product\Type
     */
    protected $_model;

    protected function setUp()
    {
        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $eventManager = $this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false);
        $downloadableFile = $this->getMockBuilder('Magento\Downloadable\Helper\File')
            ->disableOriginalConstructor()->getMock();
        $coreData = $this->getMockBuilder('Magento\Core\Helper\Data')->disableOriginalConstructor()->getMock();
        $fileStorageDb = $this->getMockBuilder('Magento\Core\Helper\File\Storage\Database')
            ->disableOriginalConstructor()->getMock();
        $filesystem = $this->getMockBuilder('Magento\Filesystem')->disableOriginalConstructor()->getMock();
        $coreRegistry = $this->getMock('Magento\Core\Model\Registry', array(), array(), '', false);
        $logger = $this->getMock('Magento\Core\Model\Logger', array(), array(), '', false);
        $productFactoryMock = $this->getMock('Magento\Catalog\Model\ProductFactory', array(), array(), '', false);
        $sampleResFactory = $this->getMock(
            'Magento\Downloadable\Model\Resource\SampleFactory', array(), array(), '', false);
        $linkResource = $this->getMock('Magento\Downloadable\Model\Resource\Link', array(), array(), '', false);
        $linksFactory = $this->getMock('Magento\Downloadable\Model\Resource\Link\Collection\Factory',
            array(), array(), '', false
        );
        $samplesFactory = $this->getMock('Magento\Downloadable\Model\Resource\Sample\CollectionFactory',
            array(), array(), '', false
        );
        $sampleFactory = $this->getMock('Magento\Downloadable\Model\SampleFactory', array(), array(), '', false);
        $linkFactory = $this->getMock('Magento\Downloadable\Model\LinkFactory', array(), array(), '', false);

        $this->_model = $objectHelper->getObject('Magento\Downloadable\Model\Product\Type', array(
            'eventManager' => $eventManager,
            'downloadableFile' => $downloadableFile,
            'coreData' => $coreData,
            'fileStorageDb' => $fileStorageDb,
            'filesystem' => $filesystem,
            'coreRegistry' => $coreRegistry,
            'logger' => $logger,
            'productFactory' => $productFactoryMock,
            'sampleResFactory' => $sampleResFactory,
            'linkResource' => $linkResource,
            'linksFactory' => $linksFactory,
            'samplesFactory' => $samplesFactory,
            'sampleFactory' => $sampleFactory,
            'linkFactory' => $linkFactory,
        ));
    }

    public function testHasWeightFalse()
    {
        $this->assertFalse($this->_model->hasWeight(), 'This product has weight, but it should not');
    }
}
