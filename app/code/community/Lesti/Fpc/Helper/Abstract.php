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

/**
 * Class Lesti_Fpc_Helper_Abstract
 */
abstract class Lesti_Fpc_Helper_Abstract extends Mage_Core_Helper_Abstract
{
    /**
     * Returns comma seperated store configs as array
     *
     * @param $path
     * @param null $store
     * @return array
     */
    public function getCSStoreConfigs($path, $store = null)
    {
        $configs = trim(Mage::getStoreConfig($path, $store));

        if ($configs) {
            return array_unique(array_map('trim', explode(',', $configs)));
        }

        return array();
    }

    /**
     * Returns data from config-xml (possibly from other modules) as array
     *
     * @param $path
     * @param null $store
     * @return array
     */
    public function getInjectedStoreConfigs($path, $store = null)
    {
        $data = Mage::getStoreConfig($path, $store);
        if(!is_array($data)) return array();

        $return = array();

        // if the config contain a value, use that, otherwise use the tag-name
        foreach ($data as $key => $value) {
            if ($value != '') {
                $return[] = $value;
            } else {
                $return[] = $key;
            }
        }

        unset($data, $key, $value);

        if (count($return)) {
            return array_unique(array_map('trim', $return));
        }

        return array();
    }
}
