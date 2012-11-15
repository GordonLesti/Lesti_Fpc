<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gordon
 * Date: 24.10.12
 * Time: 12:29
 * To change this template use File | Settings | File Templates.
 */
class Lesti_Fpc_Model_Fpc
{

    const XML_PATH_CACHE_LIFETIME = 'system/fpc/lifetime';
    protected $_cache;

    public function __construct()
    {
        $this->_cache = $this->_getCache();
    }

    public function load($key)
    {
        return $this->_cache->load($key);
    }

    public function test($key)
    {
        return $this->_cache->test($key);
    }

    public function save($body, $key, $tags = array())
    {
        $this->_cache->save($body, $key, $tags);
        return $this;
    }

    public function cleanAll()
    {
        $this->_cache->clean(Zend_Cache::CLEANING_MODE_ALL);
    }

    public function cleanByTag($tag, $cleaningMode = Zend_Cache::CLEANING_MODE_MATCHING_TAG)
    {
        if (!is_array($tag)) {
            $tag = array($tag);
        }
        $this->_cache->clean($cleaningMode, $tag);
    }

    public function isActive()
    {
        return Mage::app()->useCache('fpc');
    }

    protected function _getCache()
    {
        $frontendOptions = array(
            'lifetime' => (int) Mage::getStoreConfig(self::XML_PATH_CACHE_LIFETIME)
        );
        $backendOptions = array(
            'cache_dir' => Mage::getBaseDir('cache'),
            'file_name_prefix' => 'fpc',
            'hashed_directory_umask' => 0777,
            'hashed_directory_level' => 3
        );
        return Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }

}