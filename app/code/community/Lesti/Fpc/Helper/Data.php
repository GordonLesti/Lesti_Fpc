<?php
/**
 * Lesti_Fpc
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * http://opensource.org/licenses/OSL-3.0
 *
 * @package      Lesti_Fpc
 * @copyright    Copyright (c) 2013 Gordon Lesti (http://www.gordonlesti.com)
 * @author       Gordon Lesti <info@gordonlesti.com>
 * @license      http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
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
            foreach ($uriParams as $uriParam) {
                if ($data = $request->getParam($uriParam)) {
                    $params['uri_' . $uriParam] = $data;
                }
            }
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
            $design = Mage::getDesign();
            $params['design'] = $design->getPackageName() . '_' . $design->getTheme('template');
            if (Mage::getStoreConfig(self::XML_PATH_CUSTOMER_GROUPS)) {
                $customerSession = Mage::getSingleton('customer/session');
                $params['customer_group_id'] = $customerSession->getCustomerGroupId();
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

    /**
     * @return array
     */
    public function getCacheTags()
    {
        $fullActionName = $this->getFullActionName();
        $cacheTags = array();
        $request = Mage::app()->getRequest();
        switch ($fullActionName) {
            case 'cms_index_index' :
                $cacheTags[] = sha1('cms');
                $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_HOME_PAGE);
                if ($pageId) {
                    $cacheTags[] = sha1('cms_' . $pageId);
                }
                break;
            case 'cms_page_view' :
                $cacheTags[] = sha1('cms');
                $pageId = $request->getParam('page_id', $request->getParam('id', false));
                if ($pageId) {
                    $cacheTags[] = sha1('cms_' . $pageId);
                }
                break;
            case 'catalog_product_view' :
                $cacheTags[] = sha1('product');
                $productId = (int)$request->getParam('id');
                if ($productId) {
                    $cacheTags[] = sha1('product_' . $productId);

                    // configurable product
                    $configurableProduct = Mage::getModel('catalog/product_type_configurable');
                    // get all childs of this product and add the cache tag
                    $childIds = $configurableProduct->getChildrenIds($productId);
                    foreach ($childIds as $childIdGroup) {
                        foreach ($childIdGroup as $childId) {
                            $cacheTags[] = sha1('product_' . $childId);
                        }
                    }
                    // get all parents of this product and add the cache tag
                    $parentIds = $configurableProduct->getParentIdsByChild($productId);
                    foreach ($parentIds as $parentId) {
                        $cacheTags[] = sha1('product_' . $parentId);
                    }

                    // grouped product
                    $groupedProduct = Mage::getModel('catalog/product_type_grouped');
                    // get all childs of this product and add the cache tag
                    $childIds = $groupedProduct->getChildrenIds($productId);
                    foreach ($childIds as $childIdGroup) {
                        foreach ($childIdGroup as $childId) {
                            $cacheTags[] = sha1('product_' . $childId);
                        }
                    }
                    // get all parents of this product and add the cache tag
                    $parentIds = $groupedProduct->getParentIdsByChild($productId);
                    foreach ($parentIds as $parentId) {
                        $cacheTags[] = sha1('product_' . $parentId);
                    }

                    $categoryId = (int)$request->getParam('category', false);
                    if ($categoryId) {
                        $cacheTags[] = sha1('category');
                        $cacheTags[] = sha1('category_' . $categoryId);
                    }
                }
                break;
            case 'catalog_category_view' :
                $cacheTags[] = sha1('category');
                $categoryId = (int)$request->getParam('id', false);
                if ($categoryId) {
                    $cacheTags[] = sha1('category_' . $categoryId);
                }
                break;
        }
        Mage::dispatchEvent('fpc_helper_collect_cache_tags', array('chace_tags' => $cacheTags));
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