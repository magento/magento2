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
namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\Core\Model\Layout\Update\ValidatorFactory;
use Magento\Eav\Model\Entity\Attribute\Exception;

/**
 * Product url key attribute backend
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Customlayoutupdate extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Layout update validator factory
     *
     * @var ValidatorFactory
     */
    protected $_layoutUpdateValidatorFactory;

    /**
     * Construct the custom layout update class
     *
     * @param \Magento\Framework\Logger $logger
     * @param ValidatorFactory $layoutUpdateValidatorFactory
     */
    public function __construct(\Magento\Framework\Logger $logger, ValidatorFactory $layoutUpdateValidatorFactory)
    {
        $this->_layoutUpdateValidatorFactory = $layoutUpdateValidatorFactory;
        parent::__construct($logger);
    }

    /**
     * Validate the custom layout update
     *
     * @param \Magento\Framework\Object $object
     * @return bool
     * @throws Exception
     */
    public function validate($object)
    {
        $attributeName = $this->getAttribute()->getName();
        $xml = trim($object->getData($attributeName));

        if (!$this->getAttribute()->getIsRequired() && empty($xml)) {
            return true;
        }

        /** @var $validator \Magento\Core\Model\Layout\Update\Validator */
        $validator = $this->_layoutUpdateValidatorFactory->create();
        if (!$validator->isValid($xml)) {
            $messages = $validator->getMessages();
            //Add first message to exception
            $massage = array_shift($messages);
            $eavExc = new Exception($massage);
            $eavExc->setAttributeCode($attributeName);
            throw $eavExc;
        }
        return true;
    }
}
