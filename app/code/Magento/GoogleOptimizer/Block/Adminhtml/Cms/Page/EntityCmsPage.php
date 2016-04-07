<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Block\Adminhtml\Cms\Page;


use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GoogleOptimizer\Block\Adminhtml\EntityCodeResolverInterface;

class EntityCmsPage extends DataObject implements EntityCodeResolverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\GoogleOptimizer\Model\Code
     */
    private $codeModel;

    /**
     * Google Optimizer binded entity
     *
     * @var \Magento\Cms\Model\Page
     */
    private $entity;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\GoogleOptimizer\Model\Code $code
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\GoogleOptimizer\Model\Code $code,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->codeModel = $code;
        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        $entity = $this->getEntity();
        $this->codeModel->loadByEntityIdAndType($entity->getId(), \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PAGE);
        return $this->codeModel;
    }

    private function getEntity()
    {
        if (!$this->entity) {
            $this->entity = $this->coreRegistry->registry('cms_page');
            if (!$this->entity || !$this->entity->getId()) {
                throw new NoSuchEntityException();
            }
        }
        return $this->entity;
    }
}
