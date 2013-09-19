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
 * Class Lesti_Fpc_Model_Observer
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

    /**
     * @param $observer
     */
    public function controllerActionLayoutGenerateBlocksBefore($observer)
    {
        $fpc = $this->_getFpc();
        $request = Mage::app()->getRequest();
        if ($fpc->isActive() && !$this->_cached && $request->getParam('no_cache') !== '1') {
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

    /**
     * @param $observer
     */
    public function httpResponseSendBefore($observer)
    {
        $fpc = $this->_getFpc();
        $response = $observer->getEvent()->getResponse();
        if ($fpc->isActive() && !$this->_cached && $response->getHttpResponseCode() == 200) {
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

    /**
     * @param $observer
     */
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

    /**
     * @param $observer
     */
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

    /**
     * @param $observer
     */
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

    /**
     * @param $observer
     */
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

    /**
     * @param $observer
     */
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

    /**
     * @param $observer
     */
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

    /**
     * @param $observer
     */
    public function cataloginventoryStockItemSaveAfter($observer)
    {
        $item = $event = $observer->getEvent()->getItem();
        if ($item->getStockStatusChangedAuto()) {
            $fpc = $this->_getFpc();
            $fpc->clean(sha1('product_' . $item->getProductId()));
        }
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
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

    /**
     * @param $observer
     */
    public function applicationCleanCache($observer)
    {
        $tags = $observer->getEvent()->getTags();
        $this->_getFpc()->clean($tags);
    }

    /**
     * @param $observer
     */
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