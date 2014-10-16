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
 * Class Lesti_Fpc_Helper_Data
 */
class Lesti_Fpc_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_CACHEABLE_ACTIONS = 'system/fpc/cache_actions';
    const XML_PATH_SESSION_PARAMS = 'system/fpc/session_params';
    const XML_PATH_URI_PARAMS = 'system/fpc/uri_params';
    const XML_PATH_CUSTOMER_GROUPS = 'system/fpc/customer_groups';
    const XML_PATH_REFRESH_ACTIONS = 'system/fpc/refresh_actions';
    const XML_PATH_MISS_URI_PARAMS = 'system/fpc/miss_uri_params';
    const LAYOUT_ELEMENT_CLASS = 'Mage_Core_Model_Layout_Element';

    const REGISTRY_KEY_PARAMS = 'fpc_params';

    /**
     * @return array
     */
    public function getCacheableActions()
    {
        $actions = Mage::getStoreConfig(self::XML_PATH_CACHEABLE_ACTIONS);
        return array_map('trim', explode(',', $actions));
    }

    /**
     * @return array
     */
    public function getRefreshActions()
    {
        $actions = Mage::getStoreConfig(self::XML_PATH_REFRESH_ACTIONS);
        return array_map('trim', explode(',', $actions));
    }

    /**
     * @param string $postfix
     * @return string
     */
    public function getKey($postfix = '_page')
    {
        return sha1($this->_getParams()) . $postfix;
    }

    /**
     * @return mixed
     */
    protected function _getParams()
    {
        if (!Mage::registry(self::REGISTRY_KEY_PARAMS)) {
            $request = Mage::app()->getRequest();
            $params = array('host' => $request->getServer('HTTP_HOST'),
                'port' => $request->getServer('SERVER_PORT'),
                'full_action_name' => $this->getFullActionName());
            $uriParams = $this->_getUriParams();
            foreach ($request->getParams() as $requestParam => $requestParamValue) {
                if (!$requestParamValue) {
                    continue;
                }
                foreach ($uriParams as $uriParam) {
                    if ($this->_matchUriParam($uriParam, $requestParam)) {
                        $params['uri_' . $requestParam] = $requestParamValue;
                        break;
                    }
                }
            }
            // store
            $params = $this->_addStoreParams($params);
            // currency
            $params = $this->_addCurrencyParams($params);
            // design
            $params = $this->_addDesignParams($params);
            if (Mage::getStoreConfig(self::XML_PATH_CUSTOMER_GROUPS)) {
                $customerSession = Mage::getSingleton('customer/session');
                $params['customer_group_id'] = $customerSession
                    ->getCustomerGroupId();
            }
            $sessionParams = $this->_getSessionParams();
            $catalogSession = Mage::getSingleton('catalog/session');
            foreach ($sessionParams as $param) {
                if ($data = $catalogSession->getData($param)) {
                    $params['session_' . $param] = $data;
                }
            }
            Mage::register(self::REGISTRY_KEY_PARAMS, serialize($params));
        }
        return Mage::registry(self::REGISTRY_KEY_PARAMS);
    }

    /**
     * @param $params
     * @return mixed
     */
    protected function _addStoreParams($params)
    {
        $storeCode = Mage::app()->getStore(true)->getCode();
        if ($storeCode) {
            $params['store'] = $storeCode;
        }
        return $params;
    }

    /**
     * @param $params
     * @return mixed
     */
    protected function _addCurrencyParams($params)
    {
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        if ($currencyCode) {
            $params['currency'] = $currencyCode;
        }
        return $params;
    }

    /**
     * @param $params
     * @return mixed
     */
    protected function _addDesignParams($params)
    {
        $design = Mage::getDesign();
        $params['design'] = $design->getPackageName().'_'.
            $design->getTheme('template');
        return $params;
    }

    /**
     * Matches URI param against expression (string comparison or regular expression)
     * 
     * @param string $expression
     * @param string $param
     * @return boolean
     */
    protected function _matchUriParam($expression, $param)
    {
        if (substr($expression, 0, 1) === '/' &&
            substr($expression, -1, 1) === '/') {
            return (bool) preg_match($expression, $param);
        } else {
            return $expression === $param;
        }
    }

    /**
     * @return array
     */
    public function _getUriParams()
    {
        $params = Mage::getStoreConfig(self::XML_PATH_URI_PARAMS);
        return array_map('trim', explode(',', $params));
    }

    /**
     * @return array
     */
    protected function _getSessionParams()
    {
        $params = Mage::getStoreConfig(self::XML_PATH_SESSION_PARAMS);
        return array_map('trim', explode(',', $params));
    }

    protected function _getMissUriParams()
    {
        $params = Mage::getStoreConfig(self::XML_PATH_MISS_URI_PARAMS);
        return array_map('trim', explode(',', $params));
    }

    public function canCacheRequest()
    {
        $request = Mage::app()->getRequest();
        $missParams = $this->_getMissUriParams();
        if ($request->getMethod() != 'GET') {
            return false;
        }
        foreach ($missParams as $missParam) {
            $pair = array_map('trim', explode('=', $missParam));
            $key = $pair[0];
            $regex = (isset($pair[1])) ? $pair[1] : '';
            $param = $request->getParam($key);
            if ($param && preg_match($regex, $param)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array
     */
    public function getCacheTags()
    {
        $fullActionName = $this->getFullActionName();
        $cacheTags = array();
        $request = Mage::app()->getRequest();
        /** @var Lesti_Fpc_Helper_Data_Tag $tagHelper */
        $tagHelper = Mage::helper('fpc/data_tag');
        switch ($fullActionName) {
            case 'cms_index_index' :
                $cacheTags = $tagHelper->getCmsIndexIndexCacheTags();
                break;
            case 'cms_page_view' :
                $cacheTags = $tagHelper->getCmsPageViewCacheTags($request);
                break;
            case 'catalog_product_view' :
                $cacheTags = $tagHelper->getCatalogProductViewCacheTags($request);
                break;
            case 'catalog_category_view' :
                $cacheTags = $tagHelper->getCatalogCategoryViewCacheTags($request);
                break;
        }
        Mage::dispatchEvent(
            'fpc_helper_collect_cache_tags',
            array('cache_tags' => $cacheTags)
        );
        return $cacheTags;
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function getFullActionName($delimiter = '_')
    {
        $request = Mage::app()->getRequest();
        return $request->getRequestedRouteName() . $delimiter .
        $request->getRequestedControllerName() . $delimiter .
        $request->getRequestedActionName();
    }
}
