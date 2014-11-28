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

namespace Magento\Catalog\Model\Product\AttributeSet;


class Build
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $entityTypeId;

    /**
     * @var int
     */
    protected $skeletonId;

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Attribute\SetFactory  $attributeSetFactory
    ) {
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * @param int $entityTypeId
     * @return $this
     */
    public function setEntityTypeId($entityTypeId)
    {
        $this->entityTypeId = (int)$entityTypeId;
        return $this;
    }

    /**
     * @param int $skeletonId
     * @return $this
     */
    public function setSkeletonId($skeletonId)
    {
        $this->skeletonId = (int)$skeletonId;
        return $this;
    }

    /**
     * @param string $setName
     * @return $this
     */
    public function setName($setName)
    {
        $this->name = $setName;
        return $this;
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute\Set
     * @throws AlreadyExistsException
     */
    public function getAttributeSet()
    {
        $this->validateParameters();
        /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeSet->setEntityTypeId($this->entityTypeId)->load($this->name, 'attribute_set_name');
        if ($attributeSet->getId()) {
            throw new AlreadyExistsException();
        }

        $attributeSet->setAttributeSetName($this->name)->validate();
        $attributeSet->save();
        $attributeSet->initFromSkeleton($this->skeletonId)->save();

        return $attributeSet;
    }

    /**
     * @trows \InvalidArgumentException
     * @return void
     */
    protected function validateParameters()
    {
        if (empty($this->name)) {
            throw new \InvalidArgumentException();
        } elseif (empty($this->skeletonId)) {
            throw new \InvalidArgumentException();
        } elseif (empty($this->entityTypeId)) {
            throw new \InvalidArgumentException();
        }
    }
}
