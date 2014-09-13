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
 * Class LestiTest_TestCase
 */
abstract class LestiTest_TestCase extends PHPUnit_Framework_TestCase
{
    protected $_cacheOptions;

    protected $_cache;

    public function setUp()
    {
        Mage::init();
        $this->_cache = Mage::app()->getCacheInstance();
        $this->_cache->flush();
        $this->_cacheOptions = Mage::getResourceSingleton('core/cache')->getAllOptions();
        $cacheOptions = $this->_cacheOptions;
        foreach ($cacheOptions as $cache => $value) {
            $cacheOptions[$cache] = $cache == 'fpc' ? 1 : 0;
        }
        $this->_cache->saveOptions($cacheOptions);
    }

    public function tearDown()
    {
        $this->_cache->saveOptions($this->_cacheOptions);
    }
}
