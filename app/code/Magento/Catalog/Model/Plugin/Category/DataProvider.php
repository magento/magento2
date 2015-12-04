<?php
/**
 * Plugin for \Magento\Catalog\Model\Category\DataProvider
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Plugin\Category;

class DataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * DataProvider constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(\Magento\Framework\App\RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param array $data
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(\Magento\Catalog\Model\Category\DataProvider $subject, $data)
    {
        if (isset($data['']) && isset($data['']['general'])) {
            $data['']['general']['parent'] = (int)$this->request->getParam('parent');
        }
        return $data;
    }
}
