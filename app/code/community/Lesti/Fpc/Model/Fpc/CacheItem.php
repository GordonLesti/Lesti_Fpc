<?php

/**
 * Class Lesti_Fpc_Model_Fpc_CacheItem
 */
class Lesti_Fpc_Model_Fpc_CacheItem
{
    /**
     * @var string
     */
    private $_content;

    /**
     * @var int
     */
    private $_time;

    /**
     * @var string
     */
    private $_contentType;

    /**
     * @param string $content
     * @param int    $time
     * @param string $contentType
     */
    public function __construct($content, $time, $contentType)
    {
        $this->_content = $content;
        $this->_time = $time;
        $this->_contentType = $contentType;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->_time;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->_contentType;
    }
}
