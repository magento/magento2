<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\File\ReadInterface as FileReadInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;

/**
 * Class for encrypting data.
 */
class Cryptographer
{
    /**
     * Chunk size, in bytes, used while streaming the source file. Must be a multiple of the cipher block size.
     */
    private const STREAM_CHUNK_SIZE = 1048576;

    /**
     * Resource for handling MBI token value.
     *
     * @var AnalyticsToken
     */
    private $analyticsToken;

    /**
     * Cipher method for encryption.
     *
     * @var string
     */
    private $cipherMethod = 'AES-256-CBC';

    /**
     * @var EncodedContextFactory
     */
    private $encodedContextFactory;

    /**
     * @param AnalyticsToken $analyticsToken
     * @param EncodedContextFactory $encodedContextFactory
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        EncodedContextFactory $encodedContextFactory
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->encodedContextFactory = $encodedContextFactory;
    }

    /**
     * Encrypt input data.
     *
     * @param string $source
     * @return EncodedContext
     * @throws LocalizedException
     */
    public function encode($source)
    {
        if (!is_string($source)) {
            try {
                $source = (string)$source;
            } catch (\Exception $e) {
                throw new LocalizedException(
                    __(
                        'The data is invalid. '
                        . 'Enter the data as a string or data that can be converted into a string and try again.'
                    )
                );
            }
        } elseif (!$source) {
            throw new LocalizedException(__('The data is invalid. Enter the data as a string and try again.'));
        }
        if (!$this->validateCipherMethod($this->cipherMethod)) {
            throw new LocalizedException(__('The data is invalid. Use a valid cipher method and try again.'));
        }
        $initializationVector = $this->getInitializationVector();

        $encodedContext = $this->encodedContextFactory->create([
            'content' => openssl_encrypt(
                $source,
                $this->cipherMethod,
                $this->getKey(),
                OPENSSL_RAW_DATA,
                $initializationVector
            ),
            'initializationVector' => $initializationVector,
        ]);

        return $encodedContext;
    }

    /**
     * Encrypt data read from a source file, streaming the cipher into a destination file.
     *
     * The archive is encrypted block by block using CBC chaining, so the produced cipher is byte-for-byte
     * identical to a single-pass {@see self::encode()} and stays decryptable with the returned
     * initialization vector, while peak memory usage stays independent of the source size.
     *
     * @param FileReadInterface $source
     * @param FileWriteInterface $destination
     * @return EncodedContext
     * @throws LocalizedException
     */
    public function encodeToFile(FileReadInterface $source, FileWriteInterface $destination): EncodedContext
    {
        if (!$this->validateCipherMethod($this->cipherMethod)) {
            throw new LocalizedException(__('The data is invalid. Use a valid cipher method and try again.'));
        }

        $key = $this->getKey();
        $initializationVector = $this->getInitializationVector();
        $blockSize = openssl_cipher_iv_length($this->cipherMethod);
        $chainingVector = $initializationVector;
        $buffer = '';
        $hasData = false;
        $reachedEnd = false;

        while (!$reachedEnd) {
            $chunk = $source->read(self::STREAM_CHUNK_SIZE);
            if ($chunk === '' || $chunk === false) {
                $reachedEnd = true;
                break;
            }
            $hasData = true;
            $buffer .= $chunk;

            // Encrypt whole blocks only and always retain at least one byte, so the final pass below
            // applies PKCS7 padding exactly as a single-pass encryption of the whole source would.
            $alignedLength = intdiv(strlen($buffer) - 1, $blockSize) * $blockSize;
            if ($alignedLength <= 0) {
                continue;
            }

            $cipherChunk = openssl_encrypt(
                substr($buffer, 0, $alignedLength),
                $this->cipherMethod,
                $key,
                OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
                $chainingVector
            );
            if ($cipherChunk === false) {
                throw new LocalizedException(__('The data could not be encrypted. Try again.'));
            }
            $destination->write($cipherChunk);
            $chainingVector = substr($cipherChunk, -$blockSize);
            $buffer = substr($buffer, $alignedLength);
        }

        if (!$hasData) {
            throw new LocalizedException(__('The data is invalid. Enter the data as a string and try again.'));
        }

        $finalChunk = openssl_encrypt(
            $buffer,
            $this->cipherMethod,
            $key,
            OPENSSL_RAW_DATA,
            $chainingVector
        );
        if ($finalChunk === false) {
            throw new LocalizedException(__('The data could not be encrypted. Try again.'));
        }
        $destination->write($finalChunk);

        return $this->encodedContextFactory->create([
            'content' => '',
            'initializationVector' => $initializationVector,
        ]);
    }

    /**
     * Return key for encryption.
     *
     * @return string
     * @throws LocalizedException
     */
    private function getKey()
    {
        $token = $this->analyticsToken->getToken();
        if (!$token) {
            throw new LocalizedException(__('Enter the encryption key and try again.'));
        }
        return hash('sha256', $token);
    }

    /**
     * Return established cipher method.
     *
     * @return string
     */
    private function getCipherMethod()
    {
        return $this->cipherMethod;
    }

    /**
     * Return each time generated random initialization vector which depends on the cipher method.
     *
     * @return string
     */
    private function getInitializationVector()
    {
        $ivSize = openssl_cipher_iv_length($this->getCipherMethod());
        return openssl_random_pseudo_bytes($ivSize);
    }

    /**
     * Check that cipher method is allowed for encryption.
     *
     * @param string $cipherMethod
     * @return bool
     */
    private function validateCipherMethod($cipherMethod)
    {
        $methods = array_map(
            'strtolower',
            openssl_get_cipher_methods()
        );
        $cipherMethod = strtolower($cipherMethod);

        return (false !== array_search($cipherMethod, $methods));
    }
}
