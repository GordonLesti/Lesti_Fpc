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

    protected $_cached = false;
    protected $_html = array();
    protected $_placeholder = array();
    protected $_cache_tags = array();

    public function customerSessionInit($observer)
    {
        $fpc = $this->_getFpc();
        $key = Mage::helper('fpc')->getKey();
        if ($fpc->isActive() && !$this->_cached) {
            if ($fpc->test($key)) {
                $body = $fpc->load($key);
                $this->_cached = true;
                $layout = Mage::helper('fpc')->initLayout();
                $dynamicBlocks = Mage::helper('fpc/block')->getDynamicBlocks();
                foreach ($dynamicBlocks as $blockName) {
                    $block = $layout->getBlock($blockName);
                    if ($block) {
                        $this->_placeholder[] = Mage::helper('fpc/block')->getPlaceholderHtml($blockName);
                        $this->_html[] = $block->toHtml();
                    }
                }
                $body = str_replace($this->_placeholder, $this->_html, $body);
                Mage::app()->getResponse()->setBody($body);
                Mage::app()->getResponse()->sendResponse();
                exit;
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
                $fpc->save($body, $key, $this->_cache_tags);
                $url =Mage::getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true));
                $fpc->save($url, Mage::helper('fpc')->getKey('_url'), array('url'));
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
            $cacheableActions = Mage::helper('fpc')->getCacheableActions();
            if (in_array($fullActionName, $cacheableActions)) {
                $this->_cache_tags = array_merge(Mage::helper('fpc/block')->getCacheTags($block), $this->_cache_tags);
                if (in_array($blockName, $dynamicBlocks)) {
                    $blockName = $blockName == 'global_messages' ? 'messages' : $blockName;
                    $placeholder = Mage::helper('fpc/block')->getPlaceholderHtml($blockName);
                    $html = $observer->getTransport()->getHtml();
                    $this->_html[] = $html;
                    $this->_placeholder[] = $placeholder;
                    $observer->getTransport()->setHtml($placeholder);
                }
            }
        }
    }

    public function adminhtmlCacheRefreshType($observer)
    {
        if ($observer->getEvent()->getType() == self::CACHE_TYPE) {
            $fpc = $this->_getFpc();
            $fpc->cleanAll();
        }
    }

    public function catalogProductSaveAfter($observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getId()) {
            $fpc = $this->_getFpc();
            $fpc->cleanByTag('product_' . $product->getId());
        }
    }

    public function catalogCategorySaveAfter($observer)
    {
        $category = $observer->getEvent()->getCategory();
        if ($category->getId()) {
            $fpc = $this->_getFpc();
            $fpc->cleanByTag('category_' . $category->getId());
        }
    }

    public function cmsPageSaveAfter($observer)
    {
        $page = $observer->getEvent()->getObject();
        if ($page->getId()) {
            $fpc = $this->_getFpc();
            $tags = array('cms_' . $page->getId(),
                'cms_' . $page->getIdentifier());
            $fpc->cleanByTag($tags, Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG);
        }
    }

    public function modelSaveAfter($observer)
    {
        $object = $observer->getEvent()->getObject();
        if (get_class($object) == get_class(Mage::getModel('cms/block'))) {
            $fpc = $this->_getFpc();
            $fpc->cleanbyTag('cmsblock_' . $object->getIdentifier());
        }
    }

    protected function _getFpc()
    {
        return Mage::getSingleton('fpc/fpc');
    }

}