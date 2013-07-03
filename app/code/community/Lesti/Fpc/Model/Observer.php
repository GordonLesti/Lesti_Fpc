<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gordon
 * Date: 24.10.12
 * Time: 12:26
 * To change this template use File | Settings | File Templates.
 */
class Lesti_Fpc_Model_Observer
{
    const CACHE_TYPE = 'fpc';
    const CUSTOMER_SESSION_REGISTRY_KEY = 'fpc_customer_session';
    const SHOW_AGE_XML_PATH = 'system/fpc/show_age';

    protected $_cached = false;
    protected $_html = array();
    protected $_placeholder = array();
    protected $_cache_tags = array();

    public function controllerActionLayoutGenerateBlocksBefore($observer)
    {
        $fpc = $this->_getFpc();
        if ($fpc->isActive() && !$this->_cached) {
            $key = Mage::helper('fpc')->getKey();
            if ($object = $fpc->load($key)) {
                $object = unserialize($object);
                $body = $object['body'];
                $this->_cached = true;
                $session = Mage::getSingleton('customer/session');
                $lazyBlocks = Mage::helper('fpc/block')->getLazyBlocks();
                $dynamicBlocks = Mage::helper('fpc/block')->getDynamicBlocks();
                $blockHelper = Mage::helper('fpc/block');
                if ($blockHelper->areLazyBlocksValid()) {
                    foreach($lazyBlocks as $blockName) {
                        $this->_placeholder[] = $blockHelper->getPlaceholderHtml($blockName);
                        $this->_html[] = $session->getData('fpc_lazy_block_' . $blockName);
                    }
                } else {
                    $dynamicBlocks = array_merge($dynamicBlocks, $lazyBlocks);
                }
                $layout = $observer->getEvent()->getLayout();
                $xml = simplexml_load_string($layout->getXmlString(), Lesti_Fpc_Helper_Data::LAYOUT_ELEMENT_CLASS);
                $cleanXml = simplexml_load_string('<layout/>', Lesti_Fpc_Helper_Data::LAYOUT_ELEMENT_CLASS);
                $types = array('block', 'reference', 'action');
                foreach ($dynamicBlocks as $blockName) {
                    foreach ($types as $type) {
                        $xPath = $xml->xpath("//" . $type . "[@name='" . $blockName . "']");
                        foreach ($xPath as $child) {
                            $cleanXml->appendChild($child);
                        }
                    }
                }
                $layout->setXml($cleanXml);
                $layout->generateBlocks();
                $layout = Mage::helper('fpc/block_messages')->initLayoutMessages($layout);
                foreach ($dynamicBlocks as $blockName) {
                    $block = $layout->getBlock($blockName);
                    if ($block) {
                        $this->_placeholder[] = $blockHelper->getPlaceholderHtml($blockName);
                        $html = $block->toHtml();
                        if(in_array($blockName, $lazyBlocks)) {
                            $session->setData('fpc_lazy_block_' . $blockName, $html);
                        }
                        $this->_html[] = $html;
                    }
                }
                $body = str_replace($this->_placeholder, $this->_html, $body);
                if(Mage::getStoreConfig(self::SHOW_AGE_XML_PATH)) {
                    Mage::app()->getResponse()->setHeader('Age', time()-$time = $object['time']);
                }
                Mage::app()->getResponse()->setBody($body);
                Mage::app()->getResponse()->sendResponse();
                exit;
            }
            if(Mage::getStoreConfig(self::SHOW_AGE_XML_PATH)) {
                Mage::app()->getResponse()->setHeader('Age', 0);
            }
        }
    }

    public function httpResponseSendBefore($observer)
    {
        $fpc = $this->_getFpc();
        if ($fpc->isActive() && !$this->_cached) {
            $fullActionName = Mage::helper('fpc')->getFullActionName();
            $cacheableActions = Mage::helper('fpc')->getCacheableActions();
            if (in_array($fullActionName, $cacheableActions)) {
                $key = Mage::helper('fpc')->getKey();
                $body = $observer->getEvent()->getResponse()->getBody();
                $this->_cache_tags = array_merge(Mage::helper('fpc')->getCacheTags(), $this->_cache_tags);
                $object = array('body' => $body, 'time' => time());
                $fpc->save(serialize($object), $key, $this->_cache_tags);
                $this->_cached = true;
                $body = str_replace($this->_placeholder, $this->_html, $body);
                $observer->getEvent()->getResponse()->setBody($body);
            }
        }
    }

    public function coreBlockAbstractToHtmlAfter($observer)
    {
        $fpc = $this->_getFpc();
        if ($fpc->isActive() && !$this->_cached) {
            $fullActionName = Mage::helper('fpc')->getFullActionName();
            $block = $observer->getEvent()->getBlock();
            $blockName = $block->getNameInLayout();
            $dynamicBlocks = Mage::helper('fpc/block')->getDynamicBlocks();
            $lazyBlocks = Mage::helper('fpc/block')->getLazyBlocks();
            $dynamicBlocks = array_merge($dynamicBlocks, $lazyBlocks);
            $cacheableActions = Mage::helper('fpc')->getCacheableActions();
            if (in_array($fullActionName, $cacheableActions)) {
                $this->_cache_tags = array_merge(Mage::helper('fpc/block')->getCacheTags($block), $this->_cache_tags);
                if (in_array($blockName, $dynamicBlocks)) {
                    $placeholder = Mage::helper('fpc/block')->getPlaceholderHtml($blockName);
                    $html = $observer->getTransport()->getHtml();
                    $this->_html[] = $html;
                    $this->_placeholder[] = $placeholder;
                    $observer->getTransport()->setHtml($placeholder);
                }
            }
        }
    }

    public function controllerActionPostdispatch($observer)
    {
        $fpc = $this->_getFpc();
        if ($fpc->isActive()) {
            $fullActionName = Mage::helper('fpc')->getFullActionName();
            if (in_array($fullActionName, Mage::helper('fpc')->getRefreshActions())) {
                $session = Mage::getSingleton('customer/session');
                $session->setData(Lesti_Fpc_Helper_Block::LAZY_BLOCKS_VALID_SESSION_PARAM, false);
            }
        }
    }

    public function salesOrderPlaceAfter($observer)
    {
        $fpc = $this->_getFpc();
        if ($fpc->isActive()) {
            $order = $observer->getEvent()->getOrder();
            foreach ($order->getItemsCollection() as $item) {
                $product = $item->getProduct();
                $_model = Mage::getModel('catalog/product');
                $_product = $_model->load($product->getId());

                if (!$_product->isAvailable) {
                    $fpc->clean(sha1('product_' . $product->getId()));
                }
            }
        }
    }

    public function catalogProductSaveAfter($observer)
    {
        $fpc = $this->_getFpc();
        if ($fpc->isActive()) {
            $product = $observer->getEvent()->getProduct();
            if ($product->getId()) {
                $fpc->clean(sha1('product_' . $product->getId()));
            }
        }
    }

    public function catalogCategorySaveAfter($observer)
    {
        $fpc = $this->_getFpc();
        if ($fpc->isActive()) {
            $category = $observer->getEvent()->getCategory();
            if ($category->getId()) {
                $fpc->clean(sha1('category_' . $category->getId()));
            }
        }
    }

    public function cmsPageSaveAfter($observer)
    {
        $fpc = $this->_getFpc();
        if ($fpc->isActive()) {
            $page = $observer->getEvent()->getObject();
            if ($page->getId()) {
                $tags = array(sha1('cms_' . $page->getId()),
                    sha1('cms_' . $page->getIdentifier()));
                $fpc->clean($tags, Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG);
            }
        }
    }

    public function modelSaveAfter($observer)
    {
        $fpc = $this->_getFpc();
        if ($fpc->isActive()) {
            $object = $observer->getEvent()->getObject();
            if (get_class($object) == get_class(Mage::getModel('cms/block'))) {
                $fpc->clean(sha1('cmsblock_' . $object->getIdentifier()));
            }
        }
    }

    protected function _getFpc()
    {
        return Mage::getSingleton('fpc/fpc');
    }

    /**
     * Cron job method to clean old cache resources
     *
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function coreCleanCache($observer)
    {
        $this->_getFpc()->getFrontend()->clean(Zend_Cache::CLEANING_MODE_OLD);
    }

    public function applicationCleanCache($observer)
    {
        $tags = $observer->getEvent()->getTags();
        $this->_getFpc()->clean($tags);
    }

    public function controllerActionPredispatchAdminhtmlCacheMassRefresh($observer)
    {
        $types = Mage::app()->getRequest()->getParam('types');
        $fpc = $this->_getFpc();
        if ($fpc->isActive()) {
            if ((is_array($types) && in_array(self::CACHE_TYPE, $types)) || $types == self::CACHE_TYPE) {
                $fpc->clean();
            }
        }
    }

}