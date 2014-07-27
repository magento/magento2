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

namespace Magento\Tax\Model;

use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * Tax class model
 *
 * @method \Magento\Tax\Model\Resource\TaxClass _getResource()
 * @method \Magento\Tax\Model\Resource\TaxClass getResource()
 * @method string getClassName()
 * @method \Magento\Tax\Model\ClassModel setClassName(string $value)
 * @method string getClassType()
 * @method \Magento\Tax\Model\ClassModel setClassType(string $value)
 */
class ClassModel extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Defines Customer Tax Class string
     */
    const TAX_CLASS_TYPE_CUSTOMER = 'CUSTOMER';

    /**
     * Defines Product Tax Class string
     */
    const TAX_CLASS_TYPE_PRODUCT = 'PRODUCT';

    /**
     * @var \Magento\Tax\Model\TaxClass\Factory
     */
    protected $_classFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Model\TaxClass\Factory $classFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Model\TaxClass\Factory $classFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_classFactory = $classFactory;
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Magento\Tax\Model\Resource\TaxClass');
    }

    /**
     * Check whether this class can be deleted
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    protected function checkClassCanBeDeleted()
    {
        if (!$this->getId()) {
            throw new CouldNotDeleteException('This class no longer exists.');
        }

        $typeModel = $this->_classFactory->create($this);

        if ($typeModel->getAssignedToRules()->getSize() > 0) {
            throw new CouldNotDeleteException(
                'You cannot delete this tax class because it is used in Tax Rules.' .
                ' You have to delete the rules it is used in first.'
            );
        }

        if ($typeModel->isAssignedToObjects()) {
            throw new CouldNotDeleteException(
                'You cannot delete this tax class because it is used in existing %object(s).',
                ['object' => $typeModel->getObjectTypeName()]
            );
        }

        return true;
    }

    /**
     * Validate tax class can be deleted
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _beforeDelete()
    {
        $this->checkClassCanBeDeleted();
        return parent::_beforeDelete();
    }
}
