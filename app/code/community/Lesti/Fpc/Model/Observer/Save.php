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

/**
 * Class Lesti_Fpc_Model_Observer_Save
 */
class Lesti_Fpc_Model_Observer_Save
{
    const PRODUCT_IDS_MASS_ACTION_KEY = 'fpc_product_ids_mass_action';

    /**
     * @param $observer
     */
    public function catalogProductSaveAfter($observer)
    {
        if ($this->_getFpc()->isActive()) {
            $product = $observer->getEvent()->getProduct();
            if ($product->getId()) {
                $stockItem  = $product->getStockItem();
                $tags = array(sha1('product_' . $product->getId()));
                $parentIds = Mage::getModel('catalog/product_type_configurable')
                    ->getParentIdsByChild($product->getId());
                // Clean tag for partner products, if any
                foreach ($parentIds as $pid) {
                    $tags[] = sha1('product_' . $pid);
                }

                if ($parentIds) {
                    // Clean cache for categories on parent products if stock has changed for simple products
                    if ($stockItem && $stockItem->dataHasChangedFor('is_in_stock')) {
                        foreach ($this->_getCategories($parentIds) as $catId) {
                            $tags[] = sha1('category_' . $catId);
                        }
                    }
                }

                $this->_getFpc()->clean($tags);
                $origData = $product->getOrigData();
                if (empty($origData)
                    || (!empty($origData) && $product->dataHasChangedFor('status'))
                ) {
                    $categories = $product->getCategoryIds();
                    foreach ($categories as $categoryId) {
                        $this->_getFpc()->clean(
                            sha1('category_' . $categoryId)
                        );
                    }
                }
            }
        }
    }

    /**
     * @param $observer
     */
    public function catalogCategorySaveAfter($observer)
    {
        if ($this->_getFpc()->isActive()) {
            $category = $observer->getEvent()->getCategory();
            if ($category->getId()) {
                $this->_getFpc()->clean(sha1('category_' . $category->getId()));
            }
        }
    }

    /**
     * @param $observer
     */
    public function cmsPageSaveAfter($observer)
    {
        if ($this->_getFpc()->isActive()) {
            $page = $observer->getEvent()->getObject();
            if ($page->getId()) {
                $tags = array(sha1('cms_' . $page->getId()),
                    sha1('cms_' . $page->getIdentifier()));
                $this->_getFpc()
                    ->clean($tags, Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG);
            }
        }
    }

    /**
     * @param $observer
     */
    public function modelSaveAfter($observer)
    {
        if ($this->_getFpc()->isActive()) {
            $object = $observer->getEvent()->getObject();
            if ($object instanceof Mage_Cms_Model_Block) {
                $this->_cmsBlockSaveAfter($object);
            } elseif ($object instanceof Mage_Index_Model_Event) {
                $dataObject = $object->getDataObject();
                if ($object->getType() === 'mass_action' &&
                    $object->getEntity() === 'catalog_product' &&
                    $dataObject instanceof Mage_Catalog_Model_Product_Action) {
                    $this->_catalogProductSaveAfterMassAction(
                        $dataObject->getProductIds()
                    );
                }
            }
        }
    }

    /**
     * @param $observer
     */
    public function reviewDeleteAfter($observer)
    {
        if ($this->_getFpc()->isActive()) {
            $object = $observer->getEvent()->getObject();
            $this->_getFpc()->clean(sha1('product_' . $object->getEntityPkValue()));
        }
    }

    /**
     * @param $observer
     */
    public function reviewSaveAfter($observer)
    {
        if ($this->_getFpc()->isActive()) {
            $object = $observer->getEvent()->getObject();
            $this->_getFpc()->clean(sha1('product_' . $object->getEntityPkValue()));
        }
    }

    /**
     * @param $observer
     */
    public function cataloginventoryStockItemSaveAfter($observer)
    {
        $item = $observer->getEvent()->getItem();
        if ($item->getStockStatusChangedAuto()) {
            $this->_getFpc()->clean(sha1('product_' . $item->getProductId()));
        }
    }

    /**
     * @return Lesti_Fpc_Model_Fpc
     */
    protected function _getFpc()
    {
        return Mage::getSingleton('fpc/fpc');
    }

    /**
     * @param Mage_Cms_Model_Block $block
     */
    protected function _cmsBlockSaveAfter(Mage_Cms_Model_Block $block)
    {
        $this->_getFpc()->clean(sha1('cmsblock_'.$block->getIdentifier()));
    }

    /**
     * @param array $productIds
     */
    protected function _catalogProductSaveAfterMassAction(array $productIds)
    {
        if (!empty($productIds)) {
            $tags = array();
            foreach ($productIds as $productId) {
                $tags[] = sha1('product_' . $productId);
            }
            $this->_getFpc()->clean($tags);
        }
    }

    /**
     * @param array $producIds
     */
    protected function _getCategories($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        $catRes = Mage::getResourceModel('catalog/category');
        $table = $catRes->getTable('catalog/category_product');
        $conn = $catRes->getReadConnection();

        $query = $conn->select()->from($table, array('category_id'))->where('product_id IN (?)', $productIds);
        $results = $conn->fetchCol($query);

        return $results;
    }

}
