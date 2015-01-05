<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Core\Model\File\Storage;

use Magento\Framework\App\Response\Http;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class Response extends Http implements \Magento\Framework\App\Response\FileInterface
{
    /**
     * @var \Magento\Framework\File\Transfer\Adapter\Http
     */
    protected $_transferAdapter;

    /**
     * Full path to file
     *
     * @var string
     */
    protected $_filePath;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\Http\Context $context
     * @param \Magento\Framework\File\Transfer\Adapter\Http $transferAdapter
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\Http\Context $context,
        \Magento\Framework\File\Transfer\Adapter\Http $transferAdapter
    ) {
        parent::__construct($cookieManager, $cookieMetadataFactory, $context);
        $this->_transferAdapter = $transferAdapter;
    }

    /**
     * Send response
     *
     * @return void
     */
    public function sendResponse()
    {
        if ($this->_filePath && $this->getHttpResponseCode() == 200) {
            $this->_transferAdapter->send($this->_filePath);
        } else {
            parent::sendResponse();
        }
    }

    /**
     * @param string $path
     * @return void
     */
    public function setFilePath($path)
    {
        $this->_filePath = $path;
    }
}
