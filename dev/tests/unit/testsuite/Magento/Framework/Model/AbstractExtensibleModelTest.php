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

namespace Magento\Framework\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class AbstractExtensibleModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\AbstractExtensibleModel
     */
    protected $model;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Framework\Model\Resource\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionValidatorMock;

    protected function setUp()
    {
        $this->markTestIncomplete('Should be fixed in scope of MAGETWO-29613');
        $this->actionValidatorMock = $this->getMock(
            '\Magento\Framework\Model\ActionValidator\RemoveAction',
            array(),
            array(),
            '',
            false
        );
        $this->contextMock = new \Magento\Framework\Model\Context(
            $this->getMock('Magento\Framework\Logger', array(), array(), '', false),
            $this->getMock('Magento\Framework\Event\ManagerInterface', array(), array(), '', false),
            $this->getMock('Magento\Framework\App\CacheInterface', array(), array(), '', false),
            $this->getMock('Magento\Framework\App\State', array(), array(), '', false),
            $this->actionValidatorMock
        );
        $this->registryMock = $this->getMock('Magento\Framework\Registry', array(), array(), '', false);
        $this->resourceMock = $this->getMock(
            'Magento\Framework\Model\Resource\Db\AbstractDb',
            array(
                '_construct',
                '_getReadAdapter',
                '_getWriteAdapter',
                '__wakeup',
                'commit',
                'delete',
                'getIdFieldName',
                'rollBack'
            ),
            array(),
            '',
            false
        );
        $this->resourceCollectionMock = $this->getMock(
            'Magento\Framework\Data\Collection\Db',
            array(),
            array(),
            '',
            false
        );
        $this->model = $this->getMockForAbstractClass(
            'Magento\Framework\Model\AbstractExtensibleModel',
            array($this->contextMock, $this->registryMock, $this->resourceMock, $this->resourceCollectionMock)
        );
    }

    /**
     * Test implementation of interface for work with custom attributes.
     */
    public function testCustomAttributes()
    {
        $this->assertEquals(
            [],
            $this->model->getCustomAttributes(),
            "Empty array is expected as a result of getCustomAttributes() when custom attributes are not set."
        );
        $this->assertEquals(
            null,
            $this->model->getCustomAttribute('not_existing_custom_attribute'),
            "Null is expected as a result of getCustomAttribute(\$code) when custom attribute is not set."
        );
        $attributesAsArray = ['attribute1' => true, 'attribute2' => 'Attribute Value', 'attribute3' => 333];
        $addedAttributes = $this->addCustomAttributesToModel($attributesAsArray, $this->model);
        $this->assertEquals(
            $addedAttributes,
            $this->model->getCustomAttributes(),
            'Custom attributes retrieved from the model using getCustomAttributes() are invalid.'
        );
    }

    /**
     * Test if getData works with custom attributes as expected
     */
    public function testGetDataWithCustomAttributes()
    {
        $attributesAsArray = ['attribute1' => true, 'attribute2' => 'Attribute Value', 'attribute3' => 333];
        $modelData = ['key1' => 'value1', 'key2' => 222];
        $this->model->setData($modelData);
        $addedAttributes = $this->addCustomAttributesToModel($attributesAsArray, $this->model);
        $modelDataAsFlatArray = array_merge($modelData, $addedAttributes);
        $this->assertEquals(
            $modelDataAsFlatArray,
            $this->model->getData(),
            'All model data should be represented as a flat array, including custom attributes.'
        );
        foreach ($modelDataAsFlatArray as $field => $value) {
            $this->assertEquals(
                $value,
                $this->model->getData($field),
                "Model data item '{$field}' was retrieved incorrectly."
            );
        }
    }

    /**
     * @expectedException \LogicException
     */
    public function testRestrictedCustomAttributesGet()
    {
        $this->model->getData(\Magento\Framework\Model\AbstractExtensibleModel::CUSTOM_ATTRIBUTES_KEY);
    }

    /**
     * @expectedException \LogicException
     */
    public function testRestrictedCustomAttributesSet()
    {
        $this->model->setData(\Magento\Framework\Model\AbstractExtensibleModel::CUSTOM_ATTRIBUTES_KEY, 'value');
    }

    /**
     * @param string[] $attributesAsArray
     * @param \Magento\Framework\Model\AbstractExtensibleModel $model
     * @return \Magento\Framework\Api\AttributeInterface[]
     */
    protected function addCustomAttributesToModel($attributesAsArray, $model)
    {
        $objectManager = new ObjectManagerHelper($this);
        /** @var \Magento\Framework\Service\Data\AttributeValueBuilder $attributeValueBuilder */
        $attributeValueBuilder = $objectManager->getObject('Magento\Framework\Service\Data\AttributeValueBuilder');
        $addedAttributes = [];
        foreach ($attributesAsArray as $attributeCode => $attributeValue) {
            $addedAttributes[$attributeCode] = $attributeValueBuilder
                ->setAttributeCode($attributeCode)
                ->setValue($attributeValue)
                ->create();
            $model->setCustomAttribute($addedAttributes[$attributeCode]);
            $model->getCustomAttribute(
                $attributeCode,
                $addedAttributes[$attributeCode],
                "Custom attribute '$attributeCode' retrieved from the model is invalid."
            );
        }
        return $addedAttributes;
    }
}
