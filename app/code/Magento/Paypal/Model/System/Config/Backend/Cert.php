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
namespace Magento\Paypal\Model\System\Config\Backend;

/**
 * Backend model for saving certificate file in case of using certificate based authentication
 */
class Cert extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Paypal\Model\CertFactory
     */
    protected $_certFactory;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_tmpDirectory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Paypal\Model\CertFactory $certFactory
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Paypal\Model\CertFactory $certFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_certFactory = $certFactory;
        $this->_encryptor = $encryptor;
        $this->_tmpDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::SYS_TMP_DIR);
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
    }

    /**
     * Process additional data before save config
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value) && !empty($value['delete'])) {
            $this->setValue('');
            $this->_certFactory->create()->loadByWebsite($this->getScopeId())->delete();
        }

        if (!isset($_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'])) {
            return $this;
        }
        $tmpPath = $this->_tmpDirectory->getRelativePath(
            $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value']
        );
        if ($tmpPath && $this->_tmpDirectory->isExist($tmpPath)) {
            if (!$this->_tmpDirectory->stat($tmpPath)['size']) {
                throw new \Magento\Framework\Model\Exception(__('The PayPal certificate file is empty.'));
            }
            $this->setValue($_FILES['groups']['name'][$this->getGroupId()]['fields'][$this->getField()]['value']);
            $content = $this->_encryptor->encrypt($this->_tmpDirectory->readFile($tmpPath));
            $this->_certFactory->create()->loadByWebsite($this->getScopeId())->setContent($content)->save();
        }
        return $this;
    }

    /**
     * Process object after delete data
     *
     * @return $this
     */
    protected function _afterDelete()
    {
        $this->_certFactory->create()->loadByWebsite($this->getScopeId())->delete();
        return $this;
    }
}
