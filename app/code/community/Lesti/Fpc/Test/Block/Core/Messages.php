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
 * Class Lesti_Fpc_Test_Block_Core_Messages
 */
class Lesti_Fpc_Test_Block_Core_Messages extends Lesti_Fpc_Test_TestCase
{
    /**
     * @var Lesti_Fpc_Core_Block_Messages
     */
    protected $_messagesBlock;

    protected function setUp()
    {
        parent::setUp();
        $this->_messagesBlock = Mage::app()->getLayout()
            ->createBlock('core/messages');
    }

    /**
     * @test
     */
    public function testGetGroupedHtml()
    {
        $this->assertInstanceOf(
            'Lesti_Fpc_Core_Block_Messages',
            $this->_messagesBlock
        );
        $parentClass = get_parent_class($this->_messagesBlock);
        /** @var Mage_Core_Block_Messages $coreMessagesBlock */
        $coreMessagesBlock = new $parentClass;
        $this->assertInstanceOf(
            'Mage_Core_Block_Messages',
            $coreMessagesBlock
        );
        $expectedGroupHtml = $coreMessagesBlock->getGroupedHtml();
        $this->assertEquals(
            $expectedGroupHtml,
            $this->_messagesBlock->getGroupedHtml()
        );
        $this->assertEventDispatched(
            'core_block_messages_get_grouped_html_after'
        );
    }
}
