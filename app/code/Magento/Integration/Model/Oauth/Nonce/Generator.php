<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Oauth\Nonce;

use Magento\Framework\Oauth\ConsumerInterface;
use Magento\Framework\Oauth\NonceGeneratorInterface;

/**
 * Class \Magento\Integration\Model\Oauth\Nonce\Generator
 *
 * @since 2.0.0
 */
class Generator implements NonceGeneratorInterface
{
    /**
     * @var \Magento\Framework\Oauth\Helper\Oauth
     * @since 2.0.0
     */
    protected $_oauthHelper;

    /**
     * @var  \Magento\Integration\Model\Oauth\NonceFactory
     * @since 2.0.0
     */
    protected $_nonceFactory;

    /**
     * @var  int
     * @since 2.0.0
     */
    protected $_nonceLength;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * @since 2.0.0
     */
    protected $_date;

    /**
     * Possible time deviation for timestamp validation in seconds.
     */
    const TIME_DEVIATION = 600;

    /**
     * @param \Magento\Framework\Oauth\Helper\Oauth $oauthHelper
     * @param \Magento\Integration\Model\Oauth\NonceFactory $nonceFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param int $nonceLength - Length of the generated nonce
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Oauth\Helper\Oauth $oauthHelper,
        \Magento\Integration\Model\Oauth\NonceFactory $nonceFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $nonceLength = \Magento\Framework\Oauth\Helper\Oauth::LENGTH_NONCE
    ) {
        $this->_oauthHelper = $oauthHelper;
        $this->_nonceFactory = $nonceFactory;
        $this->_date = $date;
        $this->_nonceLength = $nonceLength;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function generateNonce(ConsumerInterface $consumer = null)
    {
        return $this->_oauthHelper->generateRandomString($this->_nonceLength);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function generateTimestamp()
    {
        return $this->_date->timestamp();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function validateNonce(ConsumerInterface $consumer, $nonce, $timestamp)
    {
        try {
            $timestamp = (int)$timestamp;
            if ($timestamp <= 0 || $timestamp > time() + self::TIME_DEVIATION) {
                throw new \Magento\Framework\Oauth\OauthInputException(
                    __('Incorrect timestamp value in the oauth_timestamp parameter')
                );
            }

            /** @var \Magento\Integration\Model\Oauth\Nonce $nonceObj */
            $nonceObj = $this->_nonceFactory->create()->loadByCompositeKey($nonce, $consumer->getId());

            if ($nonceObj->getNonce()) {
                throw new \Magento\Framework\Oauth\Exception(
                    __(
                        'The nonce is already being used by the consumer with ID %1',
                        [$consumer->getId()]
                    )
                );
            }

            $nonceObj->setNonce($nonce)->setConsumerId($consumer->getId())->setTimestamp($timestamp)->save();
        } catch (\Magento\Framework\Oauth\Exception $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Oauth\Exception(__('An error occurred validating the nonce'));
        }
    }
}
