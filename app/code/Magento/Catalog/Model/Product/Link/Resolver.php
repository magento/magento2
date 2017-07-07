<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Link;

/**
 * @api
 */
class Resolver
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var null|array
     */
    protected $links = null;

    /**
     * Resolver constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Get stored value.
     * Fallback to request if none.
     *
     * @return array|null
     */
    public function getLinks()
    {
        if (null === $this->links) {
            $this->links = (array)$this->request->getParam('links', []);
        }
        return $this->links;
    }

    /**
     * Override link data from request
     *
     * @param array|null $links
     * @return void
     */
    public function override($links)
    {
        $this->links = $links;
    }
}
