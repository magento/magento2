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
namespace Magento\Bundle\Service\V1\Product\Option\Type;

use Magento\Bundle\Model\Source\Option\Type as TypeModel;
use Magento\Bundle\Service\V1\Data\Product\Option\Type;
use Magento\Bundle\Service\V1\Data\Product\Option\TypeConverter;

class ReadService implements ReadServiceInterface
{
    /**
     * @var TypeModel
     */
    private $type;

    /**
     * @var TypeConverter
     */
    private $typeConverter;

    /**
     * @param TypeModel $type
     * @param TypeConverter $typeConverter
     */
    public function __construct(TypeModel $type, TypeConverter $typeConverter)
    {
        $this->type = $type;
        $this->typeConverter = $typeConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes()
    {
        $optionList = $this->type->toOptionArray();

        /** @var Type[] $typeDtoList */
        $typeDtoList = [];
        foreach ($optionList as $option) {
            $typeDtoList[] = $this->typeConverter->createDataFromModel($option);
        }
        return $typeDtoList;
    }
}
