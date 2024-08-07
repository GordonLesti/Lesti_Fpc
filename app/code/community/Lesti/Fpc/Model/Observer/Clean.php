<?php
/**
 * Lesti_Fpc (http:gordonlesti.com/lestifpc)
 *
 * PHP version 5
 *
 * @link      https://github.com/GordonLesti/Lesti_Fpc
 * @package   Lesti_Fpc
 * @author    Gordon Lesti <info@gordonlesti.com>
 * @copyright Copyright (c) 2013-2016 Gordon Lesti (http://gordonlesti.com)
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

    public function flushFpc(Varien_Event_Observer $observer)
    {
        // type only exist for event adminhtml_cache_refresh_type
        $type = $observer->getEvent()->getData('type');
        if (! empty($type) && ($type !== self::CACHE_TYPE || ! $this->_getFpc()->isActive())) {
            return;
        }

        $this->_getFpc()->clean();
    }

    protected function _getFpc(): Lesti_Fpc_Model_Fpc
    {
        return Mage::getSingleton('fpc/fpc');
    }
}
