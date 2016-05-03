<?php

class ConsistentHash
{
    private $server = array();
    public $ring = array();
    private $virtualPointNumber = 0;


    /**
     * @param array  $server               host数组
     * @param integer $virtualPointNumber  每个节点的虚拟节点个数
     */
    public function __construct($server, $virtualPointNumber = 0)
    {
        if (is_array($server))
        {
            if (0 !== $virtualPointNumber)
            {
                $this->virtualPointNumber = $virtualPointNumber;
            }

            $this->server = $server;
            $this->setServer();
        }
    }

    //生成hash环
    public function setServer()
    {
        if ($this->virtualPointNumber)
        {
            foreach ($this->server as $value)
            {
                for ($i = 0; $i < $this->virtualPointNumber; ++$i)
                {
                    $this->ring[$this->_unsignedCrc32($value . '-' . $i)] = $value;
                }
                $this->ring[$this->_unsignedCrc32($value)] = $value;
            }
        } else {
            foreach ($this->server as $value)
            {
                $this->ring[$this->_unsignedCrc32($value)] = $value;
            }
        }

        ksort($this->ring);
    }

    public function get($key)
    {
        $keyCrc = $this->_unsignedCrc32($key);
        foreach ($this->ring as $key => $value)
        {
            if ($keyCrc <= $key)
            {
                return $this->ring[$key];
            }
        }
    }

    //字符串的32位无符号的CRC
    private function _unsignedCrc32($value)
    {
        return abs(crc32($value));
    }
}
