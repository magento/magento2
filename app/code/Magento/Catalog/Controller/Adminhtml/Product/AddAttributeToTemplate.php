<?php
/**
 *
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
namespace Magento\Catalog\Controller\Adminhtml\Product;

class AddAttributeToTemplate extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Framework\Controller\Result\JSONFactory
     */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\Controller\Result\JSONFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\Controller\Result\JSONFactory $resultJsonFactory
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultJsonFactory = $resultJsonFactory;
    }
    /**
     * Add attribute to product template
     *
     * @return \Magento\Framework\Controller\Result\JSON
     */
    public function execute()
    {
        $request = $this->getRequest();
        $resultJson = $this->resultJsonFactory->create();
        try {
            /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
            $attribute = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute')
                ->load($request->getParam('attribute_id'));

            $attributeSet = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\Set')
                ->load($request->getParam('template_id'));

            /** @var \Magento\Eav\Model\Resource\Entity\Attribute\Group\Collection $attributeGroupCollection */
            $attributeGroupCollection = $this->_objectManager->get(
                'Magento\Eav\Model\Resource\Entity\Attribute\Group\Collection'
            );
            $attributeGroupCollection->setAttributeSetFilter($attributeSet->getId());
            $attributeGroupCollection->addFilter('attribute_group_code', $request->getParam('group'));
            $attributeGroupCollection->setPageSize(1);

            $attributeGroup = $attributeGroupCollection->getFirstItem();

            $attribute->setAttributeSetId($attributeSet->getId())->loadEntityAttributeIdBySet();

            $attribute->setEntityTypeId($attributeSet->getEntityTypeId())
                ->setAttributeSetId($request->getParam('template_id'))
                ->setAttributeGroupId($attributeGroup->getId())
                ->setSortOrder('0')
                ->save();

            $resultJson->setJsonData($attribute->toJson());
        } catch (\Exception $e) {
            $response = new \Magento\Framework\Object();
            $response->setError(false);
            $response->setMessage($e->getMessage());
            $resultJson->setJsonData($response->toJson());
        }
        return $resultJson;
    }
}
