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
 * Class Lesti_Fpc_Helper_Data_Tag
 */
class Lesti_Fpc_Helper_Data_Tag extends Mage_Core_Helper_Abstract
{
    /**
     * @return array
     */
    public static function getCmsIndexIndexCacheTags()
    {
        $cacheTags = array();
        $cacheTags[] = sha1('cms');
        $pageId = Mage::getStoreConfig(
            Mage_Cms_Helper_Page::XML_PATH_HOME_PAGE
        );
        if ($pageId) {
            $cacheTags[] = sha1('cms_' . $pageId);
        }
        return $cacheTags;
    }

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @return array
     */
    public static function getCmsPageViewCacheTags(Mage_Core_Controller_Request_Http $request)
    {
        $cacheTags = array();
        $cacheTags[] = sha1('cms');
        $pageId = $request->getParam(
            'page_id',
            $request->getParam('id', false)
        );
        if ($pageId) {
            $cacheTags[] = sha1('cms_' . $pageId);
        }
        return $cacheTags;
    }

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @return array
     */
    public static function getCatalogProductViewCacheTags(Mage_Core_Controller_Request_Http $request)
    {
        $cacheTags = array();
        $cacheTags[] = sha1('product');
        $productId = (int)$request->getParam('id');
        if ($productId) {
            $cacheTags[] = sha1('product_' . $productId);

            // configurable product
            $configurableProduct = Mage::getModel(
                'catalog/product_type_configurable'
            );
            // get all childs of this product and add the cache tag
            $childIds = $configurableProduct->getChildrenIds($productId);
            foreach ($childIds as $childIdGroup) {
                foreach ($childIdGroup as $childId) {
                    $cacheTags[] = sha1('product_' . $childId);
                }
            }
            // get all parents of this product and add the cache tag
            $parentIds = $configurableProduct
                ->getParentIdsByChild($productId);
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
        return $cacheTags;
    }

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @return array
     */
    public static function getCatalogCategoryViewCacheTags(Mage_Core_Controller_Request_Http $request)
    {
        $cacheTags = array();
        $cacheTags[] = sha1('category');
        $categoryId = (int)$request->getParam('id', false);
        if ($categoryId) {
            $cacheTags[] = sha1('category_' . $categoryId);
        }
        return $cacheTags;
    }
}
