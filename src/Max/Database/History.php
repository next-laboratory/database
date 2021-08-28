<?php

namespace Max\Database;

class History extends \Max\Collection
{

    public function end()
    {
        return end($this->items);
    }

}
