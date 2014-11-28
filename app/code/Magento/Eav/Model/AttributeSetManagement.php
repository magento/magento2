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
namespace Magento\Eav\Model;

use \Magento\Eav\Api\AttributeSetManagementInterface;
use \Magento\Eav\Api\AttributeSetRepositoryInterface;
use \Magento\Eav\Api\Data\AttributeSetInterface;
use \Magento\Eav\Model\Config as EavConfig;
use \Magento\Framework\Exception\InputException;

class AttributeSetManagement implements AttributeSetManagementInterface
{
    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $repository;

    /**
     * @param Config $eavConfig
     * @param AttributeSetRepositoryInterface $repository
     */
    public function __construct(
        EavConfig $eavConfig,
        AttributeSetRepositoryInterface $repository
    ) {
        $this->eavConfig = $eavConfig;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function create($entityTypeCode, AttributeSetInterface $attributeSet, $skeletonId)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
        if ($attributeSet->getId() !== null) {
            throw InputException::invalidFieldValue('id', $attributeSet->getId());
        }
        if ($skeletonId == 0) {
            throw InputException::invalidFieldValue('skeletonId', $skeletonId);
        }
        // Make sure that skeleton attribute set is valid (try to load it)
        $this->repository->get($skeletonId);

        try {
            $attributeSet->setEntityTypeId($this->eavConfig->getEntityType($entityTypeCode)->getId());
            $attributeSet->validate();
        } catch (\Exception $exception) {
            throw new InputException($exception->getMessage());
        }

        $this->repository->save($attributeSet);
        $attributeSet->initFromSkeleton($skeletonId);

        return $this->repository->save($attributeSet);
    }
}
