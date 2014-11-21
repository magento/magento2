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
namespace Magento\Framework\Data;

class AbstractDataObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $subObjectData = ['subKey' => 'subValue'];
        $nestedObjectData = ['nestedKey' => 'nestedValue'];
        $result = [
            'key' => 'value',
            'object' => $subObjectData,
            'nestedArray' => ['nestedObject' => $nestedObjectData]
        ];

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $subObject = $objectManager->getObject('Magento\Framework\Data\Stub\DataObject');
        $subObject->setData($subObjectData);

        $nestedObject = $objectManager->getObject('Magento\Framework\Data\Stub\DataObject');
        $nestedObject->setData($nestedObjectData);

        $dataObject = $objectManager->getObject('Magento\Framework\Data\Stub\DataObject');
        $data = ['key' => 'value', 'object' => $subObject, 'nestedArray' => ['nestedObject' => $nestedObject]];
        $dataObject->setData($data);

        $this->assertEquals($result, $dataObject->toArray());
    }

    public function testGet()
    {
        $key = 'key';
        $value = 'value';
        $data = [$key => $value];

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $dataObject = $objectManager->getObject('Magento\Framework\Data\Stub\DataObject');
        $dataObject->setData($data);

        $this->assertEquals($value, $dataObject->get($key));
    }
}
 