<?php
/**
 * 
 * 
 * 
 * 
 */

namespace MagentoHackathon\Composer\Magento;


class ProjectConfig {
    
    protected $libraryPath;
    protected $libraryPackages;
    
    public function __construct( $extra )
    {
        $this->applyDeprecatedRootConfigs( $extra );
        if( isset($extra['magento-project']) ){
            $this->applyMagentoConfig($extra['magento-project']);
        }
    }
    
    protected function fetchVarFromConfigArray( $array, $key, $default=null ){
        $result = $default;
        if( isset($array[$key]) ){
            $result = $array[$key];
        }
        return $result;
    }
    
    protected function applyDeprecatedRootConfigs( $rootConfig )
    {
        
    }
    
    protected function applyMagentoConfig( $config )
    {
        $this->libraryPath          = $this->fetchVarFromConfigArray( $config, 'libraryPath');
        $this->libraryPackages      = $this->fetchVarFromConfigArray( $config, 'libraries');
        
    }
    
    public function getLibraryPath()
    {
        return $this->libraryPath;
    }
    
    public function getLibraryConfigByPackagename( $packagename )
    {
        return $this->fetchVarFromConfigArray( $this->libraryPackages, $packagename );
    }
    
    
    

}
