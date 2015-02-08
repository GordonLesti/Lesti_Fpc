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
 * Class Lesti_Fpc_Test_Helper_Block_Messages
 */
class Lesti_Fpc_Test_Helper_Block_Messages extends Lesti_Fpc_Test_TestCase
{
    /**
     * @var Lesti_Fpc_Helper_Block_Messages
     */
    protected $_messagesHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->_messagesHelper = Mage::helper('fpc/block_messages');
    }

    /**
     * @test
     */
    public function testInitLayoutMessagesEmpty()
    {
        $layout = Mage::app()->getLayout();
        $this->_messagesHelper->initLayoutMessages($layout);
        $this->assertEmpty($layout->getMessagesBlock()->getMessages());
    }

    /**
     * @test
     */
    public function testInitLayoutMessagesCatalog()
    {
        $layout = Mage::app()->getLayout();
        /** @var Mage_Catalog_Model_Session $catalogStorage */
        $catalogStorage = Mage::getSingleton('catalog/session');
        $catalogStorage->addSuccess('Fpc is cool.');
        $catalogStorage->addError('Fpc has no errors.');
        $this->assertInstanceOf(
            'Mage_Core_Model_Layout',
            $this->_messagesHelper->initLayoutMessages($layout)
        );

        // test if session is now empty
        $this->assertEquals(0, $catalogStorage->getMessages()->count());
        $messages = $layout->getMessagesBlock()->getMessages();
        $this->assertCount(2, $messages);
    }

    /**
     * @test
     */
    public function testInitLayoutMessagesTag()
    {
        $layout = Mage::app()->getLayout();
        /** @var Mage_Tag_Model_Session $tagStorage */
        $tagStorage = Mage::getSingleton('tag/session');
        $tagStorage->addSuccess('Fpc is cool.');
        $tagStorage->addError('Fpc has no errors.');
        $this->assertInstanceOf(
            'Mage_Core_Model_Layout',
            $this->_messagesHelper->initLayoutMessages($layout)
        );

        // test if session is now empty
        $this->assertEquals(0, $tagStorage->getMessages()->count());
        $messages = $layout->getMessagesBlock()->getMessages();
        $this->assertCount(2, $messages);
    }

    /**
     * @test
     */
    public function testInitLayoutMessagesCheckout()
    {
        $layout = Mage::app()->getLayout();
        /** @var Mage_Checkout_Model_Session $checkoutStorage */
        $checkoutStorage = Mage::getSingleton('checkout/session');
        $checkoutStorage->addSuccess('Fpc is cool.');
        $checkoutStorage->addError('Fpc has no errors.');
        $this->assertInstanceOf(
            'Mage_Core_Model_Layout',
            $this->_messagesHelper->initLayoutMessages($layout)
        );

        // test if session is now empty
        $this->assertEquals(0, $checkoutStorage->getMessages()->count());
        $messages = $layout->getMessagesBlock()->getMessages();
        $this->assertCount(2, $messages);
    }

    /**
     * @test
     */
    public function testInitLayoutMessagesCustomer()
    {
        $layout = Mage::app()->getLayout();
        /** @var Mage_Customer_Model_Session $customerStorage */
        $customerStorage = Mage::getSingleton('customer/session');
        $customerStorage->addSuccess('Fpc is cool.');
        $customerStorage->addError('Fpc has no errors.');
        $this->assertInstanceOf(
            'Mage_Core_Model_Layout',
            $this->_messagesHelper->initLayoutMessages($layout)
        );

        // test if session is now empty
        $this->assertEquals(0, $customerStorage->getMessages()->count());
        $messages = $layout->getMessagesBlock()->getMessages();
        $this->assertCount(2, $messages);
    }

    /**
     * @test
     * @expectedException Mage_Core_exception
     * @expectedExceptionMessage Invalid messages storage "fpc/session"
     * for layout messages initialization
     */
    public function testInitLayoutInvalidStorage()
    {
        $layout = Mage::app()->getLayout();
        $this->_messagesHelper
            ->initLayoutMessages($layout, array('fpc/session'));
    }
}
