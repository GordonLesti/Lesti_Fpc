<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gordon
 * Date: 13.11.12
 * Time: 13:08
 * To change this template use File | Settings | File Templates.
 */
class Lesti_Fpc_Helper_Block_Messages extends Mage_Core_Helper_Abstract
{

    public function initLayoutMessages($layout)
    {
        $messagesStorage = array('catalog/session',
            'tag/session',
            'checkout/session');
        foreach ($messagesStorage as $storageName) {
            $storage = Mage::getSingleton($storageName);
            if ($storage) {
                $block = $layout->getMessagesBlock();
                $block->addMessages($storage->getMessages(true));
                $block->setEscapeMessageFlag($storage->getEscapeMessages(true));
                $block->addStorageType($storageName);
            } else {
                Mage::throwException(
                    Mage::helper('core')->__('Invalid messages storage "%s" for layout messages initialization', (string)$storageName)
                );
            }
        }
        return $layout;
    }

}