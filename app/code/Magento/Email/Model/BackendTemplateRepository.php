<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Email\Model;

class BackendTemplateRepository implements \Magento\Email\Api\BackendTemplateRepositoryInterface
{
    
    /**
     * @var \Magento\Email\Model\ResourceModel\Template
     */
    private $resource;
    
    /**
     * @param ResourceModel\Template $resource
     */
    public function __construct(\Magento\Email\Model\ResourceModel\Template $resource)
    {
        $this->resource = $resource;
    }
    
    /**
     * @param \Magento\Framework\Mail\TemplateInterface $template
     *
     * @return \Magento\Framework\Mail\TemplateInterface|mixed
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magento\Framework\Mail\TemplateInterface $template)
    {
        try {
            $this->resource->save($template);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Could not save the template: %1', $exception->getMessage()),
                $exception
            );
        }
        
        return $template;
    }
}
