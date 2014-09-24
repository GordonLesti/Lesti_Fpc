<?php
/**
 * Lesti_Fpc (http:gordonlesti.com/lestifpc)
 *
 * PHP version 5
 *
 * @link      https://github.com/GordonLesti/Lesti_Fpc
 * @package   Lesti_Fpc
 * @author    Gordon Lesti <info@gordonlesti.com>
 * @copyright Copyright (c) 2013-2014 Gordon Lesti (http://gordonlesti.com)
 * @license   http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

class Lesti_Fpc_Model_Observer_Clean
{
    const CACHE_TYPE = 'fpc';

    /**
     * Cron job method to clean old cache resources
     */
    public function coreCleanCache()
    {
        $this->_getFpc()->getFrontend()->clean(Zend_Cache::CLEANING_MODE_OLD);
    }

    public function controllerActionPredispatchAdminhtmlCacheMassRefresh()
    {
        $types = Mage::app()->getRequest()->getParam('types');
        if ($this->_getFpc()->isActive()) {
            if ((is_array($types) && in_array(self::CACHE_TYPE, $types)) ||
                $types == self::CACHE_TYPE) {
                $this->_getFpc()->clean();
            }
        }
    }

    /**
     * @return Lesti_Fpc_Model_Fpc
     */
    protected function _getFpc()
    {
        return Mage::getSingleton('fpc/fpc');
    }
}
