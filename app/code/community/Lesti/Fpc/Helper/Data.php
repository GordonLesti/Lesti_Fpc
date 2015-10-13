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
class Lesti_Fpc_Helper_Data extends Lesti_Fpc_Helper_Abstract
{
    const XML_PATH_CACHEABLE_ACTIONS = 'system/fpc/cache_actions';
    const XML_PATH_BYPASS_HANDLES = 'system/fpc/bypass_handles';
    const XML_PATH_URI_PARAMS = 'system/fpc/uri_params';
    const XML_PATH_URI_PARAMS_LAYERED_NAVIGATION = 'system/fpc/uri_params_layered_navigation';
    const XML_PATH_CUSTOMER_GROUPS = 'system/fpc/customer_groups';
    const XML_PATH_REFRESH_ACTIONS = 'system/fpc/refresh_actions';
    const XML_PATH_MISS_URI_PARAMS = 'system/fpc/miss_uri_params';
    const LAYOUT_ELEMENT_CLASS = 'Mage_Core_Model_Layout_Element';
    const CACHE_KEY_LAYERED_NAVIGATION_ATTRIBUTES = 'layeredNavigationAttributes';

    const REGISTRY_KEY_PARAMS = 'fpc_params';

    // List of pages that contain layered navigation
    static protected $_pagesWithLayeredNavigation = array(
        'catalogsearch_result_index',
        'catalog_category_layered',
        'catalog_category_view'
    );

    /**
     * @return array
     */
    public function getCacheableActions()
    {
        return $this->getCSStoreConfigs(self::XML_PATH_CACHEABLE_ACTIONS);
    }

    /**
     * @return array
     */
    public function getBypassHandles()
    {
        return $this->getCSStoreConfigs(self::XML_PATH_BYPASS_HANDLES);
    }

    /**
     * @return array
     */
    public function getRefreshActions()
    {
        return $this->getCSStoreConfigs(self::XML_PATH_REFRESH_ACTIONS);
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
                'full_action_name' => $this->getFullActionName(),
                'ajax' => $request->isAjax(),
              );
            $uriParams = $this->_getUriParams();
            foreach ($request->getParams() as $requestParam =>
                     $requestParamValue) {
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
            if (Mage::getStoreConfig(self::XML_PATH_CUSTOMER_GROUPS)) {
                $customerSession = Mage::getSingleton('customer/session');
                $params['customer_group_id'] = $customerSession
                    ->getCustomerGroupId();
            }

            // edit parameters via event
            $parameters = new Varien_Object();
            $parameters->setValue($params);
            Mage::dispatchEvent(
                'fpc_helper_collect_params',
                array('parameters' => $parameters)
            );
            $params = $parameters->getValue();


            Mage::register(self::REGISTRY_KEY_PARAMS, serialize($params));
        }
        return Mage::registry(self::REGISTRY_KEY_PARAMS);
    }

    /**
     * Matches URI param against expression
     * (string comparison or regular expression)
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
        $configParams = $this->getCSStoreConfigs(self::XML_PATH_URI_PARAMS);

        if (Mage::getStoreConfig(self::XML_PATH_URI_PARAMS_LAYERED_NAVIGATION)) {
            $layeredNavigationParams = $this->_getLayeredNavigationAttributes();
        } else {
            $layeredNavigationParams = array();
        }

        return array_merge($configParams, $layeredNavigationParams);
    }

    /**
     * If on a page that contains a layered navigation block, load all attributes that are supposed to show
     *
     * @return array
     */
    protected function _getLayeredNavigationAttributes()
    {
        // List of attributes that are used in layered navigation
        $layeredNavigationAttributes = array();

        $currentFullActionName = $this->getFullActionName();
        if (in_array($currentFullActionName, self::$_pagesWithLayeredNavigation)) {
            /** @var Mage_Catalog_Model_Resource_Product_Attribute_Collection $attributeCollection */
            $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection');

            // The category and search pages may have different filterable attributes, based on how the attributes
            // are configured
            switch ($currentFullActionName) {
                case 'catalogsearch_result_index':
                    $filterableField = 'is_filterable_in_search';
                    break;
                case 'catalog_category_layered':
                case 'catalog_category_view':
                default:
                    $filterableField = 'is_filterable';
            }

            $cache = Mage::app()->getCache();
            $cacheId = self::CACHE_KEY_LAYERED_NAVIGATION_ATTRIBUTES.'_'.$filterableField;
            $cacheTags = array('FPC', self::CACHE_KEY_LAYERED_NAVIGATION_ATTRIBUTES);
            $layeredNavigationAttributesCache = $cache->load($cacheId);

            if (!$layeredNavigationAttributesCache) {
                $attributeCollection->addFieldToFilter($filterableField, true);
                foreach ($attributeCollection as $attribute) {
                    $layeredNavigationAttributes[] = $attribute->getAttributeCode();
                }
                $cache->save(serialize($layeredNavigationAttributes), $cacheId, $cacheTags);
            } else {
                $layeredNavigationAttributes = unserialize($layeredNavigationAttributesCache);
            }
        }

        return $layeredNavigationAttributes;
    }

    /**
     * @return array
     */
    protected function _getMissUriParams()
    {
        return $this->getCSStoreConfigs(self::XML_PATH_MISS_URI_PARAMS);
    }

    /**
     * @return bool
     */
    public function canCacheRequest()
    {
        $request = Mage::app()->getRequest();
        if (strtoupper($request->getMethod()) != 'GET') {
            return false;
        }
        $missParams = $this->_getMissUriParams();
        foreach ($missParams as $missParam) {
            $pair = array_map('trim', explode('=', $missParam));
            $key = $pair[0];
            $param = $request->getParam($key);
            if ($param && isset($pair[1]) && preg_match($pair[1], $param)) {
                return false;
            }
        }

        $handles = Mage::app()->getLayout()->getUpdate()->getHandles();
        foreach ($this->getBypassHandles() as $handle) {
            if (in_array($handle, $handles)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getFullActionName()
    {
        $delimiter = '_';
        $request = Mage::app()->getRequest();
        return $request->getRequestedRouteName() . $delimiter .
        $request->getRequestedControllerName() . $delimiter .
        $request->getRequestedActionName();
    }
}
