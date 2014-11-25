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
                $this->_getFpc()->clean(sha1('product_' . $product->getId()));

                $origData = $product->getOrigData();
                if (empty($origData) ||
                    (!empty($origData) &&
                        $product->getStatus() != $origData['status'])) {
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
}
