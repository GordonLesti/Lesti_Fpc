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
 * Class Lesti_Fpc_Model_Observer
 */
class Lesti_Fpc_Model_Observer
{
    const CUSTOMER_SESSION_REGISTRY_KEY = 'fpc_customer_session';
    const SHOW_AGE_XML_PATH = 'system/fpc/show_age';
    const FORM_KEY_PLACEHOLDER = '<!-- fpc form_key_placeholder -->';
    const SESSION_ID_PLACEHOLDER = '<!-- fpc session_id_placeholder -->';

    protected $_cached = false;
    protected $_html = array();
    protected $_placeholder = array();
    protected $_cacheTags = array();

    /**
     * @param $observer
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function controllerActionLayoutGenerateBlocksBefore($observer)
    {
        if ($this->_getFpc()->isActive() &&
            !$this->_cached &&
            Mage::helper('fpc')->canCacheRequest()) {
            $key = Mage::helper('fpc')->getKey();
            if ($object = $this->_getFpc()->load($key)) {
                $time = (int)substr($object, 0, 10);
                $body = substr($object, 10);
                $this->_cached = true;
                $session = Mage::getSingleton('customer/session');
                $lazyBlocks = Mage::helper('fpc/block')->getLazyBlocks();
                $dynamicBlocks = Mage::helper('fpc/block')->getDynamicBlocks();
                $blockHelper = Mage::helper('fpc/block');
                if ($blockHelper->areLazyBlocksValid()) {
                    foreach ($lazyBlocks as $blockName) {
                        $this->_placeholder[] = $blockHelper
                            ->getPlaceholderHtml($blockName);
                        $this->_html[] = $session
                            ->getData('fpc_lazy_block_' . $blockName);
                    }
                } else {
                    $dynamicBlocks = array_merge($dynamicBlocks, $lazyBlocks);
                }
                // prepare Layout
                $layout = $this->_prepareLayout(
                    $observer->getEvent()->getLayout(),
                    $dynamicBlocks
                );
                // insert dynamic blocks
                $this->_insertDynamicBlocks(
                    $layout,
                    $session,
                    $dynamicBlocks,
                    $lazyBlocks
                );
                $this->_placeholder[] = self::SESSION_ID_PLACEHOLDER;
                $this->_html[] = $session->getEncryptedSessionId();
                $this->_replaceFormKey();
                $body = str_replace($this->_placeholder, $this->_html, $body);
                if (Mage::getStoreConfig(self::SHOW_AGE_XML_PATH)) {
                    Mage::app()->getResponse()
                        ->setHeader('Age', time() - $time);
                }
                $response = Mage::app()->getResponse();
                $response->setBody($body);
                Mage::dispatchEvent(
                    'fpc_http_response_send_before',
                    array('response' => $response)
                );
                $response->sendResponse();
                exit;
            }
            if (Mage::getStoreConfig(self::SHOW_AGE_XML_PATH)) {
                Mage::app()->getResponse()->setHeader('Age', 0);
            }
        }
    }

    /**
     * @param $observer
     */
    public function httpResponseSendBefore($observer)
    {
        $response = $observer->getEvent()->getResponse();
        if ($this->_getFpc()->isActive() &&
            !$this->_cached &&
            Mage::helper('fpc')->canCacheRequest() &&
            $response->getHttpResponseCode() == 200) {
            $fullActionName = Mage::helper('fpc')->getFullActionName();
            $cacheableActions = Mage::helper('fpc')->getCacheableActions();
            if (in_array($fullActionName, $cacheableActions)) {
                $key = Mage::helper('fpc')->getKey();
                $body = $observer->getEvent()->getResponse()->getBody();
                $session = Mage::getSingleton('core/session');
                $formKey = $session->getFormKey();
                if ($formKey) {
                    $body = str_replace(
                        $formKey,
                        self::FORM_KEY_PLACEHOLDER,
                        $body
                    );
                    $this->_placeholder[] = self::FORM_KEY_PLACEHOLDER;
                    $this->_html[] = $formKey;
                }
                $sid = $session->getEncryptedSessionId();
                if ($sid) {
                    $body = str_replace(
                        $sid,
                        self::SESSION_ID_PLACEHOLDER,
                        $body
                    );
                    $this->_placeholder[] = self::SESSION_ID_PLACEHOLDER;
                    $this->_html[] = $sid;
                }
                // edit cacheTags via event
                $cacheTags = new Varien_Object();
                $cacheTags->setValue($this->_cacheTags);
                Mage::dispatchEvent(
                    'fpc_observer_collect_cache_tags',
                    array('cache_tags' => $cacheTags)
                );
                $this->_cacheTags = $cacheTags->getValue();
                $this->_getFpc()->save(time() . $body, $key, $this->_cacheTags);
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
        if ($this->_getFpc()->isActive() &&
            !$this->_cached &&
            Mage::helper('fpc')->canCacheRequest()) {
            $fullActionName = Mage::helper('fpc')->getFullActionName();
            $block = $observer->getEvent()->getBlock();
            $blockName = $block->getNameInLayout();
            $dynamicBlocks = Mage::helper('fpc/block')->getDynamicBlocks();
            $lazyBlocks = Mage::helper('fpc/block')->getLazyBlocks();
            $dynamicBlocks = array_merge($dynamicBlocks, $lazyBlocks);
            $cacheableActions = Mage::helper('fpc')->getCacheableActions();
            if (in_array($fullActionName, $cacheableActions)) {
                $this->_cacheTags = array_merge(
                    Mage::helper('fpc/block')->getCacheTags($block),
                    $this->_cacheTags
                );
                if (in_array($blockName, $dynamicBlocks)) {
                    $placeholder = Mage::helper('fpc/block')
                        ->getPlaceholderHtml($blockName);
                    $html = $observer->getTransport()->getHtml();
                    $this->_html[] = $html;
                    $this->_placeholder[] = $placeholder;
                    $observer->getTransport()->setHtml($placeholder);
                }
            }
        }
    }

    public function controllerActionPostdispatch()
    {
        if ($this->_getFpc()->isActive()) {
            $fullActionName = Mage::helper('fpc')->getFullActionName();
            if (in_array(
                $fullActionName,
                Mage::helper('fpc')->getRefreshActions()
            )) {
                $session = Mage::getSingleton('customer/session');
                $session->setData(
                    Lesti_Fpc_Helper_Block::LAZY_BLOCKS_VALID_SESSION_PARAM,
                    false
                );
            }
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
     * @param Mage_Core_Model_Layout $layout
     * @param array $dynamicBlocks
     * @return Mage_Core_Model_Layout
     */
    protected function _prepareLayout(
        Mage_Core_Model_Layout $layout,
        array $dynamicBlocks
    )
    {
        $xml = simplexml_load_string(
            $layout->getXmlString(),
            Lesti_Fpc_Helper_Data::LAYOUT_ELEMENT_CLASS
        );
        $cleanXml = simplexml_load_string(
            '<layout/>',
            Lesti_Fpc_Helper_Data::LAYOUT_ELEMENT_CLASS
        );
        $types = array('block', 'reference', 'action');
        foreach ($dynamicBlocks as $blockName) {
            foreach ($types as $type) {
                $xPath = $xml->xpath(
                    "//" . $type . "[@name='" . $blockName . "']"
                );
                foreach ($xPath as $child) {
                    $cleanXml->appendChild($child);
                }
            }
        }
        $layout->setXml($cleanXml);
        $layout->generateBlocks();
        return Mage::helper('fpc/block_messages')
            ->initLayoutMessages($layout);
    }

    protected function _replaceFormKey()
    {
        $coreSession = Mage::getSingleton('core/session');
        $formKey = $coreSession->getFormKey();
        if ($formKey) {
            $this->_placeholder[] = self::FORM_KEY_PLACEHOLDER;
            $this->_html[] = $formKey;
        }
    }

    /**
     * @param Mage_Core_Model_Layout $layout
     * @param Mage_Customer_Model_Session $session
     * @param array $dynamicBlocks
     * @param array $lazyBlocks
     */
    protected function _insertDynamicBlocks(
        Mage_Core_Model_Layout &$layout,
        Mage_Customer_Model_Session &$session,
        array &$dynamicBlocks,
        array &$lazyBlocks
    )
    {
        foreach ($dynamicBlocks as $blockName) {
            $block = $layout->getBlock($blockName);
            if ($block) {
                $this->_placeholder[] = Mage::helper('fpc/block')
                    ->getPlaceholderHtml($blockName);
                $html = $block->toHtml();
                if (in_array($blockName, $lazyBlocks)) {
                    $session->setData('fpc_lazy_block_' . $blockName, $html);
                }
                $this->_html[] = $html;
            }
        }
    }
}