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
    const XML_PATH_CURL_MAX_REQUEST = 'system/fpc/curl_max_request';
    const TABLE_FPC_URL = 'fpc_url';
    const LOG_FPC = 'fpc.log';
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
        if(Mage::helper('fpc')->rebuildCache()) {
            $url =Mage::getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true));
            $this->_cache->save($url, Mage::helper('fpc')->getKey('_url'), array('url'));
            $this->_removeUrlsFromRebuild(array($url));
        }
        return $this;
    }

    public function cleanAll()
    {
        if(Mage::helper('fpc')->rebuildCache()) {
            $keys = $this->_cache->getIdsNotMatchingTags(array('url'));
            $this->_addUrlsToRebuild($keys);
        }
        $this->_cache->clean(Zend_Cache::CLEANING_MODE_ALL);
    }

    public function cleanByTag($tag, $cleaningMode = Zend_Cache::CLEANING_MODE_MATCHING_TAG)
    {
        if(Mage::helper('fpc')->rebuildCache()) {
            if (!is_array($tag)) {
                $tag = array($tag);
            }
            if ($cleaningMode == Zend_Cache::CLEANING_MODE_MATCHING_TAG) {
                $keys = $this->_cache->getIdsMatchingTags($tag);
            } else if ($cleaningMode == Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG) {
                $keys = $this->_cache->getIdsMatchingAnyTags($tag);
            }
            $this->_addUrlsToRebuild($keys);
        }
        $this->_cache->clean($cleaningMode, $tag);
    }

    public function cleanOld()
    {
        $this->_cache->clean(Zend_Cache::CLEANING_MODE_OLD);
    }

    public function isActive()
    {
        return Mage::app()->useCache('fpc');
    }

    public function rebuild()
    {
        $connection = $this->_getConnection('core_read');
        $select = $connection ->select();
        $select->from(self::TABLE_FPC_URL)
            ->limit((int) Mage::getStoreConfig(self::XML_PATH_CURL_MAX_REQUEST));
        $urls = $connection->fetchAll($select);
        $this->_removeUrlsFromRebuild($urls);
        foreach ($urls as $url) {
            $ch = curl_init($url['url']);
            curl_exec($ch);
            curl_close($ch);
        }
    }

    protected function _getConnection($type)
    {
        return $resource = Mage::getSingleton('core/resource')->getConnection($type);
    }

    protected function _removeUrlsFromRebuild($urls)
    {
        $connection = $this->_getConnection('core_write');
        $connection->delete(self::TABLE_FPC_URL, array('url IN(?)' => $urls));
    }

    protected function _addUrlsToRebuild($keys)
    {
        $connection = $this->_getConnection('core_write');
        $insert = array();
        foreach ($keys as $key) {
            $key = substr($key, 0, -5) . '_url';
            if ($this->test($key)) {
                $insert[] = array('url' => $this->load($key));
                $this->_cache->remove($key);
            }
        }
        try {
            $connection->insertMultiple(self::TABLE_FPC_URL, $insert);
        } catch (Exception $e) {
            Mage::log(Mage::helper('fpc')->__('Problems by adding Keys: ' . implode(', ', $keys)), null, self::LOG_FPC);
            return false;
        }
        return true;
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