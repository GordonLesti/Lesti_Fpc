<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gordon
 * Date: 15.05.13
 * Time: 17:59
 * To change this template use File | Settings | File Templates.
 */
class Lesti_Fpc_Core_Block_Messages extends Mage_Core_Block_Messages
{
    /**
     * Retrieve messages in HTML format grouped by type
     *
     * @param   string $type
     * @return  string
     */
    public function getGroupedHtml()
    {
        $html = parent::getGroupedHtml();

        /**
         * Use single transport object instance for all blocks
         */

        $_transportObject = new Varien_Object;
        $_transportObject->setHtml($html);
        Mage::dispatchEvent('core_block_messages_get_grouped_html_after',
            array('block' => $this, 'transport' => $_transportObject));
        $html = $_transportObject->getHtml();

        return $html;
    }
}