<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gordon
 * Date: 03.04.13
 * Time: 14:49
 * To change this template use File | Settings | File Templates.
 *
 * if you want the welcome-message to be dynamic please replace in
 * page/html/header.phtml <?php echo $this->getWelcome() ?> with <?php echo $this->getChildHtml('header.welcome') ?>
 */
class Lesti_Fpc_Block_Page_Html_Header_Welcome extends Mage_Core_Block_Abstract
{

    protected function _toHtml()
    {
        if (empty($this->_data['welcome'])) {
            if (Mage::isInstalled() && Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->_data['welcome'] = $this->__('Welcome, %s!', $this->escapeHtml(Mage::getSingleton('customer/session')->getCustomer()->getName()));
            } else {
                $this->_data['welcome'] = Mage::getStoreConfig('design/header/welcome');
            }
        }

        return $this->_data['welcome'];
    }

}