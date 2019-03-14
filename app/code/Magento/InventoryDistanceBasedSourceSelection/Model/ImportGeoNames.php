<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\HTTP\ClientInterface;
use Magento\InventoryDistanceBasedSourceSelection\Model\ResourceModel\UpdateGeoNames;

/**
 * Import geonames
 */
class ImportGeoNames
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var File
     */
    private $file;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var UpdateGeoNames
     */
    private $updateGeoNames;

    /**
     * @var string
     */
    private $geoNamesBaseUrl;

    /**
     * ImportGeoNames constructor.
     *
     * @param ClientInterface $client
     * @param Filesystem $filesystem
     * @param File $file
     * @param UpdateGeoNames $updateGeoNames
     * @param string $geoNamesBaseUrl
     */
    public function __construct(
        ClientInterface $client,
        Filesystem $filesystem,
        File $file,
        UpdateGeoNames $updateGeoNames,
        string $geoNamesBaseUrl
    ) {
        $this->client = $client;
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->updateGeoNames = $updateGeoNames;
        $this->geoNamesBaseUrl = $geoNamesBaseUrl;
    }

    /**
     * Download a country
     *
     * @param string $countryCode
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function downloadCountry(string $countryCode): string
    {
        $countryZipFile = $this->geoNamesBaseUrl . $countryCode. '.zip';
        $this->client->get($countryZipFile);

        $varDir = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $exportPath = $varDir->getAbsolutePath('geonames');
        $this->file->mkdir($exportPath, 0770, true);

        $destinationFile = $exportPath . '/' . $countryCode. '.zip';
        $this->file->write($destinationFile, $this->client->getBody());

        return $destinationFile;
    }

    /**
     * In memory extract ZIP file to string
     *
     * @param string $zipFile
     * @param string $countryCode
     * @return string
     * @throws LocalizedException
     */
    private function unpackZipFile(string $zipFile, string $countryCode): string
    {
        $zipArchive = new \ZipArchive();
        $res = $zipArchive->open($zipFile);
        if ($res !== true) {
            throw new LocalizedException(__('Cannot download country'));
        }

        $resource = $zipArchive->getStream($countryCode . '.txt');

        $contents = '';
        while (!feof($resource)) {
            $contents .= fread($resource, 1024);
        }

        return $contents;
    }

    /**
     * Import TSV file
     *
     * @param string $tsvContent
     * @param string $countryCode
     * @return int
     */
    private function importTsv(string $tsvContent, string $countryCode): int
    {
        $lines = preg_split('/[\r\n]+/', $tsvContent);

        $geoNames = [];
        foreach ($lines as $line) {
            $parts = explode("\t", $line);
            if (count($parts) < 10) {
                continue;
            }

            $geoNames[] = [
                'country_code' => $countryCode,
                'postcode' => $parts[1],
                'city' => $parts[2],
                'region' => $parts[3],
                'province' => $parts[6],
                'latitude' => (float) $parts[9],
                'longitude' => (float) $parts[10],
            ];
        }

        $this->updateGeoNames->execute($geoNames, $countryCode);
        return count($geoNames);
    }

    /**
     * Import geonames and return the amount of items
     *
     * @param string $countryCode
     * @return int
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute(string $countryCode): int
    {
        $countryCode = strtoupper(preg_replace('/\W/', '', $countryCode));
        if (!$countryCode) {
            throw new LocalizedException(__('Undefined country code'));
        }

        $zipFile = $this->downloadCountry($countryCode);
        $tsvFile = $this->unpackZipFile($zipFile, $countryCode);

        return $this->importTsv($tsvFile, $countryCode);
    }
}
