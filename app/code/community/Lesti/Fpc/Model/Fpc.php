<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gordon
 * Date: 24.10.12
 * Time: 12:29
 * To change this template use File | Settings | File Templates.
 */
class Lesti_Fpc_Model_Fpc extends Mage_Core_Model_Cache
{
    const CACHE_TAG = 'FPC';

    /**
     * Default iotions for default backend
     *
     * @var array
     */
    protected $_defaultBackendOptions = array(
        'hashed_directory_level'    => 3,
        'hashed_directory_umask'    => 0777,
        'file_name_prefix'          => 'fpc',
    );

    public function __construct()
    {
        $options = Mage::getConfig()->getNode('global/fpc')->asArray();
        parent::__construct($options);
    }

    /**
     * Save data
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param int $lifeTime
     * @return bool
     */
    public function save($data, $id, $tags=array(), $lifeTime=null)
    {
        /**
         * Add global magento cache tag to all cached data exclude config cache
         */
        if (!in_array(Mage_Core_Model_Config::CACHE_TAG, $tags)) {
            $tags[] = self::CACHE_TAG;
        }
        if ($this->_disallowSave) {
            return true;
        }
        return $this->_frontend->save((string)$data, $this->_id($id), $this->_tags($tags), $lifeTime);
    }

    /**
     * Clean cached data by specific tag
     *
     * @param   array $tags
     * @return  bool
     */
    public function clean($tags=array())
    {
        $mode = Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG;
        if (!empty($tags)) {
            if (!is_array($tags)) {
                $tags = array($tags);
            }
            $res = $this->_frontend->clean($mode, $this->_tags($tags));
        } else {
            $res = $this->_frontend->clean($mode, array(self::CACHE_TAG));
            $res = $res && $this->_frontend->clean($mode, array(Mage_Core_Model_Config::CACHE_TAG));
        }
        return $res;
    }

    public function isActive()
    {
        return Mage::app()->useCache('fpc');
    }

}