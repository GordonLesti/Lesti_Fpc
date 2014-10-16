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
                        $this->_getFpc()->clean(sha1('category_' . $categoryId));
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
                $this->_getFpc()->clean($tags, Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG);
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
            if (get_class($object) == get_class(Mage::getModel('cms/block'))) {
                $this->_getFpc()->clean(sha1('cmsblock_' . $object->getIdentifier()));
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
     * @todo I guess there is a easier solution
     * @param $observer
     */
    public function catalogProductMassActionBefore($observer)
    {
        if ($this->_getFpc()->isActive()) {
            $entities = $observer->getEvent()->getData();
            $productIds = $entities['product_ids'];

            $coreSession = Mage::getSingleton('core/session');

            $currentProductIds = $coreSession
                ->getData(self::PRODUCT_IDS_MASS_ACTION_KEY);
            if (!empty($currentProductIds)) {
                $productIds = array_merge($currentProductIds, $productIds);
            }

            $coreSession->setData(self::PRODUCT_IDS_MASS_ACTION_KEY, $productIds);
        }
    }

    /**
     * @todo I guess there is a easier solution
     */
    public function catalogProductMassActionAfter()
    {
        if ($this->_getFpc()->isActive()) {
            $productIds = Mage::getSingleton('core/session')
                ->getData(self::PRODUCT_IDS_MASS_ACTION_KEY, true);

            foreach ($productIds as $productId) {
                $this->_getFpc()->clean(sha1('product_' . $productId));
            }
        }
    }
}