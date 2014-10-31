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
namespace Magento\Catalog\Controller\Adminhtml\Product\Attribute;

class Validate extends \Magento\Catalog\Controller\Adminhtml\Product\Attribute
{
    /**
     * @var \Magento\Framework\Controller\Result\JSONFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Cache\FrontendInterface $attributeLabelCache
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JSONFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Cache\FrontendInterface $attributeLabelCache,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JSONFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context, $attributeLabelCache, $coreRegistry, $resultPageFactory);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = new \Magento\Framework\Object();
        $response->setError(false);

        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $frontendLabel = $this->getRequest()->getParam('frontend_label');
        $attributeCode = $attributeCode ?: $this->generateCode($frontendLabel[0]);
        $attributeId = $this->getRequest()->getParam('attribute_id');
        $attribute = $this->_objectManager->create(
            'Magento\Catalog\Model\Resource\Eav\Attribute'
        )->loadByCode(
            $this->_entityTypeId,
            $attributeCode
        );

        if ($attribute->getId() && !$attributeId) {
            if (strlen($this->getRequest()->getParam('attribute_code'))) {
                $response->setAttributes(array('attribute_code' => __('An attribute with this code already exists.')));
            } else {
                $response->setAttributes(
                    array('attribute_label' => __('Attribute with the same code (%1) already exists.', $attributeCode))
                );
            }
            $response->setError(true);
        }
        if ($this->getRequest()->has('new_attribute_set_name')) {
            $setName = $this->getRequest()->getParam('new_attribute_set_name');
            /** @var $attributeSet \Magento\Eav\Model\Entity\Attribute\Set */
            $attributeSet = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\Set');
            $attributeSet->setEntityTypeId($this->_entityTypeId)->load($setName, 'attribute_set_name');
            if ($attributeSet->getId()) {
                $setName = $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($setName);
                $this->messageManager->addError(__('Attribute Set with name \'%1\' already exists.', $setName));

                $layout = $this->layoutFactory->create();
                $layout->initMessages();
                $response->setError(true);
                $response->setHtmlMessage($layout->getMessagesBlock()->getGroupedHtml());
            }
        }
        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }
}
