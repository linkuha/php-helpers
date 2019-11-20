<?php
/**
 * Created by PhpStorm.
 * User: pudich
 * Date: 12.10.2018
 * Time: 10:25
 */

namespace SimpleLibs;

class ConfigBase implements \ArrayAccess
{

    public function __construct(array $values = array())
    {
        foreach ($values as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    public function offsetSet($id, $value)
    {
        if (isset($this[$id])) {
            throw new \Exception($id);
        }

        $this[$id] = $value;
    }

    public function offsetGet($id)
    {
        if (!isset($this[$id])) {
            throw new \Exception($id);
        }

        $val = $this[$id];
        return $val;
    }


    public function offsetExists($id)
    {
        return isset($this[$id]);
    }

    public function offsetUnset($id)
    {
        if (isset($this[$id])) {
            unset($this[$id]);
        }
    }
}
