<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Result;

use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\Controller\AbstractResult;

/**
 * A result that contains raw response - may be good for passing through files,
 * returning result of downloads or some other binary contents
 * @since 2.0.0
 */
class Raw extends AbstractResult
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $contents;

    /**
     * @param string $contents
     * @return $this
     * @since 2.0.0
     */
    public function setContents($contents)
    {
        $this->contents = $contents;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function render(HttpResponseInterface $response)
    {
        $response->setBody($this->contents);
        return $this;
    }
}
