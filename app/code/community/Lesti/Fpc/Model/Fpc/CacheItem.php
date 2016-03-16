<?php

/**
 * Class Lesti_Fpc_Model_Fpc_CacheItem
 */
class Lesti_Fpc_Model_Fpc_CacheItem
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var int
     */
    private $time;

    /**
     * @var string
     */
    private $contentType;

    /**
     * @param string $content
     * @param int    $time
     * @param string $contentType
     */
    public function __construct($content, $time, $contentType)
    {
        $this->content = $content;
        $this->time = $time;
        $this->contentType = $contentType;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }
}
