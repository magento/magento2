<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Block\Adminhtml\Cms\Page;


use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GoogleOptimizer\Block\Adminhtml\EntityCodeResolverInterface;
use Magento\GoogleOptimizer\Model\Code as GoogleOptimizerCode;

class EntityCmsPage extends DataObject
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var GoogleOptimizerCode
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
     * @param GoogleOptimizerCode $code
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        GoogleOptimizerCode $code,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->codeModel = $code;
        parent::__construct($data);
    }

    /**
     * Returns Code Model
     *
     * @return GoogleOptimizerCode|null
     * @throws NoSuchEntityException
     */
    public function getCode()
    {
        $code = null;
        $entity = $this->getEntity();
        if ($entity->getId()) {
            $this->codeModel->loadByEntityIdAndType($entity->getId(), GoogleOptimizerCode::ENTITY_TYPE_PAGE);
            $code = $this->codeModel;
        }
        return $code;
    }

    /**
     * Retrieves Google Optimizer binded entity
     *
     * @return \Magento\Cms\Model\Page|mixed
     * @throws NoSuchEntityException
     */
    private function getEntity()
    {
        if (!$this->entity) {
            $this->entity = $this->coreRegistry->registry('cms_page');
            if (!$this->entity) {
                throw new NoSuchEntityException();
            }
        }
        return $this->entity;
    }
}
