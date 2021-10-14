<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File\Test\Unit;

use Magento\Framework\File\Uploader;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test class for \Magento\Framework\File\Uploader
 */
class UploaderTest extends TestCase
{
    /**
     * Uploader model
     *
     * @var \Magento\Framework\File\Uploader
     */
    protected $_model;

    /**
     * @param string $fileName
     * @param string|bool $expectedCorrectedFileName
     *
     * @dataProvider getCorrectFileNameProvider
     */
    public function testGetCorrectFileName($fileName, $expectedCorrectedFileName)
    {
        $isExceptionExpected = $expectedCorrectedFileName === true;

        if ($isExceptionExpected) {
            $this->expectException(\LengthException::class);
        }

        $this->assertEquals(
            $expectedCorrectedFileName,
            Uploader::getCorrectFileName($fileName)
        );
    }

    /**
     * @return array
     */
    public function getCorrectFileNameProvider()
    {
        return [
            [
                '^&*&^&*^$$$$()',
                'file.'
            ],
            [
                '^&*&^&*^$$$$().png',
                'file.png'
            ],
            [
                '_',
                'file.'
            ],
            [
                '_.jpg',
                'file.jpg'
            ],
            [
                'a.' . str_repeat('b', 88),
                'a.' . str_repeat('b', 88)
            ],
            [
                'a.' . str_repeat('b', 89),
                true
            ]
        ];
    }

    /**
     *
     */
    public function testFileSingleUpload()
    {
        $sysTmpFolder = sys_get_temp_dir();
        $fileId = "testimagepngfile";
        $uploaded_filename = $sysTmpFolder . "/" . $fileId;
        $imagestring = "data:image/webp;base64,UklGRoYEAABXRUJQVlA4IHoEAACQKgCdASoAAQABPkkkjUUioiETPpSQKASEsrdwtyDMUgcmO4Pxb8o9zG63+Of5R5L/YfzA+QP6/9vHzs/zP+A9of/A9QD9KOln5gP13/ZD3hv9V1AH9G6kL+n+oB+wHlAfD5+437Ae0BqwPV0DE1vv5JOEs6L1cvSRuOfknd3d3dL1tpwlnResJQR+NHPyTu7u6XrbThLOi9YSgj7idUK6lLlFRs5REdk5bl1Cj4V10Dc6/VbAeI5hP2Mz7Ad8NflHEg1OcMNThZsibQMuI5i+BBgcNgDZAGRilVaV97A63fEnhUsHAh+34kOZ7o4X/NqLPuk87UBQeB22PMUG5SyELop/1fp/OOReuY8ErnnlvVt5sy2FbAeIwhYjFZokINgg1aQVjlogGfSINxAUU6ZAyHOcWKUnm561l1ixXd3d3d3dL1tpwlnResJQR+NHPyTu7u6XrbThLOi9YSWAAP13AdVi8Xqe4VLs+URR2fUf81LdBdC0wcP/sRaAAKcF6uXyM88AoR/IygHdNUBL/8aQC7jOX6aXAOyBc1K2VQ+CPyDp09nbHKYJkoS95ltNPTkGdZFlnr+0URtVLKWlmoBjRoJBnLkxgPBan+q/7v0sFaAgqUTPVARxi0KeBvyE2fYvtN0wXfsYpll2ZyTFaWv74g+NvwOIorfxppmtw1Aoe44JrkNfy92+kxmHl09JB5IWndyUZRPg0ENUORNjjrfwR53QUrAv0Pbw3+KzCsmRClX/6j5qHi+MfbVkmbixgheNw1TRP+3vwHXXuzEZM9TBO9XANE2AzPm878LDSXDOwItzSeQesOJ62rm1UQvWaVDgH2Kn7M+8xwg+T/+OjC+iY095CFjS9okvc+qsvdTPVwAlO7LtBI0EIPzRzF5Gp/c/CFXvKUexvfh0wdgpZaH/mhYsOOYQ1+soKHvHXb8NNEASvkpeRQQ4cgkQeZ/n+265EAlN9vhsykU8uXhZMStw7o1Idw9eCuibOJ1q9fsUHxPsvdcdyKs6wWk+nD/AQNd6QoTqJzEJdsktuk7OmIPUMoHaVM8kQapQgGTe7ZzbSoFUgTNBpbI/v54p/QDvwLZtdANku5UFSccarNnogJ7N+vS0JAcldFM4Xktoll84uqcC+2xZIBAGyrliTzRiLwm2CMjXgif2ln3Kjoyhg7+VIHP5u6jkHupENMNqzDQCwxlAIZ/mZp6PTaakjeTLsEPPPQlP+HapL566KF1CLCjgDgyWkOPLlDPmi8UrpWJM4p9ujO2RAd5UT9KuIOrTRPTaFnL/Z3OmIwwnw9UKaRMXJyeGQL01KGd2+xR8WYSiFsv70JlpBI3+srAMzPIUll3LDCN7HfKyg9b34wav8xZWi8KcRgfRmRUtZ1y9JCMD+sTeve2bR0WxgWADnPLeEHRzPVh2b/invd69EQjklxr6vYcUllQuKf2NXXbpTXnPgBlUX+mCJFcAhtrgHCTQggsxeQT14ylsCgRtMvJHskl979QMxjLIwAF44RAQMYccBwblWgDiDoAAAAA=";
        $data = file_put_contents($uploaded_filename, base64_decode($imagestring));

        $_FILES = [ $fileId => [
            "name"=>"testimage.png",
            "type"=> "image/png",
            "tmp_name"=>$uploaded_filename,
            "error"=>0,
            "size"=>1166
        ]];

        $this->_model = new Uploader($fileId);

        $this->assertEquals(
            file_exists($uploaded_filename),
            true
        );

    }

    /**
     *
     */
    public function testFileMultipleUpload()
    {
        $sysTmpFolder = sys_get_temp_dir();
        $fileId = "testimagepngfile";
        $uploaded_filename = $sysTmpFolder . "/" . $fileId;
        $imagestring = "data:image/webp;base64,UklGRoYEAABXRUJQVlA4IHoEAACQKgCdASoAAQABPkkkjUUioiETPpSQKASEsrdwtyDMUgcmO4Pxb8o9zG63+Of5R5L/YfzA+QP6/9vHzs/zP+A9of/A9QD9KOln5gP13/ZD3hv9V1AH9G6kL+n+oB+wHlAfD5+437Ae0BqwPV0DE1vv5JOEs6L1cvSRuOfknd3d3dL1tpwlnResJQR+NHPyTu7u6XrbThLOi9YSgj7idUK6lLlFRs5REdk5bl1Cj4V10Dc6/VbAeI5hP2Mz7Ad8NflHEg1OcMNThZsibQMuI5i+BBgcNgDZAGRilVaV97A63fEnhUsHAh+34kOZ7o4X/NqLPuk87UBQeB22PMUG5SyELop/1fp/OOReuY8ErnnlvVt5sy2FbAeIwhYjFZokINgg1aQVjlogGfSINxAUU6ZAyHOcWKUnm561l1ixXd3d3d3dL1tpwlnResJQR+NHPyTu7u6XrbThLOi9YSWAAP13AdVi8Xqe4VLs+URR2fUf81LdBdC0wcP/sRaAAKcF6uXyM88AoR/IygHdNUBL/8aQC7jOX6aXAOyBc1K2VQ+CPyDp09nbHKYJkoS95ltNPTkGdZFlnr+0URtVLKWlmoBjRoJBnLkxgPBan+q/7v0sFaAgqUTPVARxi0KeBvyE2fYvtN0wXfsYpll2ZyTFaWv74g+NvwOIorfxppmtw1Aoe44JrkNfy92+kxmHl09JB5IWndyUZRPg0ENUORNjjrfwR53QUrAv0Pbw3+KzCsmRClX/6j5qHi+MfbVkmbixgheNw1TRP+3vwHXXuzEZM9TBO9XANE2AzPm878LDSXDOwItzSeQesOJ62rm1UQvWaVDgH2Kn7M+8xwg+T/+OjC+iY095CFjS9okvc+qsvdTPVwAlO7LtBI0EIPzRzF5Gp/c/CFXvKUexvfh0wdgpZaH/mhYsOOYQ1+soKHvHXb8NNEASvkpeRQQ4cgkQeZ/n+265EAlN9vhsykU8uXhZMStw7o1Idw9eCuibOJ1q9fsUHxPsvdcdyKs6wWk+nD/AQNd6QoTqJzEJdsktuk7OmIPUMoHaVM8kQapQgGTe7ZzbSoFUgTNBpbI/v54p/QDvwLZtdANku5UFSccarNnogJ7N+vS0JAcldFM4Xktoll84uqcC+2xZIBAGyrliTzRiLwm2CMjXgif2ln3Kjoyhg7+VIHP5u6jkHupENMNqzDQCwxlAIZ/mZp6PTaakjeTLsEPPPQlP+HapL566KF1CLCjgDgyWkOPLlDPmi8UrpWJM4p9ujO2RAd5UT9KuIOrTRPTaFnL/Z3OmIwwnw9UKaRMXJyeGQL01KGd2+xR8WYSiFsv70JlpBI3+srAMzPIUll3LDCN7HfKyg9b34wav8xZWi8KcRgfRmRUtZ1y9JCMD+sTeve2bR0WxgWADnPLeEHRzPVh2b/invd69EQjklxr6vYcUllQuKf2NXXbpTXnPgBlUX+mCJFcAhtrgHCTQggsxeQT14ylsCgRtMvJHskl979QMxjLIwAF44RAQMYccBwblWgDiDoAAAAA=";
        $data = file_put_contents($uploaded_filename, base64_decode($imagestring));

        $_FILES =
            [$fileId=>[
                "name"=>["testimage.png",""],
                "type"=>["image/png",""],
                "tmp_name"=>[$uploaded_filename,""],
                "error"=>[0,4],
                "size"=>[1166,0]
            ]];

        $fileId=[
            "name"=>"testimage.png",
            "type"=> "image/png",
            "tmp_name"=>$uploaded_filename,
            "error"=>0,
            "size"=>1166
        ];

        $this->_model = new Uploader($fileId);

        $this->assertEquals(
            file_exists($uploaded_filename),
            true
        );

    }
}
