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
 * Class Lesti_Fpc_Model_Observer_Parameters
 */
class Lesti_Fpc_Model_Observer_Parameters
{
    /**
     * @param $observer
     */
    public function fpcHelperCollectParams($observer)
    {
        $params = array();
        // store
        $storeCode = Mage::app()->getStore(true)->getCode();
        if ($storeCode) {
            $params['store'] = $storeCode;
        }
        // currency
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        if ($currencyCode) {
            $params['currency'] = $currencyCode;
        }
        // design
        $design = Mage::getDesign();
        $params['design'] = $design->getPackageName().'_'.
            $design->getTheme('template');

        $parameters = $observer->getEvent()->getParameters();
        $additionalParams = $parameters->getValue();
        $additionalParams = array_merge($additionalParams, $params);
        $parameters->setValue($additionalParams);
    }
}
