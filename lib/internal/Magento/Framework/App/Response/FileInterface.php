<?php
/**
 * Interface of response sending file content
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\Response;

interface FileInterface extends HttpInterface
{
    /**
     * Set path to the file being sent
     *
     * @param string $path
     * @return void
     */
    public function setFilePath($path);
}
