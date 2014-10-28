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
namespace Magento\Catalog\Service\V1\Product\AttributeSet;

use Magento\Catalog\Service\V1\Data\Eav\AttributeSet;

/**
 * Interface WriteServiceInterface
 * Service interface to create/update/remove product attribute sets
 */
interface WriteServiceInterface
{
    /**
     * Create attribute set from data
     *
     * @param \Magento\Catalog\Service\V1\Data\Eav\AttributeSet $attributeSet
     * @param int $skeletonId
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function create(AttributeSet $attributeSet, $skeletonId);

    /**
     * Update attribute set data
     *
     * @param \Magento\Catalog\Service\V1\Data\Eav\AttributeSet $attributeSetData
     * @return int attribute set ID
     * @throws \Magento\Framework\Model\Exception If attribute set is not found
     */
    public function update(AttributeSet $attributeSetData);

    /**
     * Remove attribute set by id
     *
     * @param int $attributeSetId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @return bool
     */
    public function remove($attributeSetId);
}
