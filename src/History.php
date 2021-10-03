<?php

namespace Max\Database;

class History extends Collection
{

    public function end()
    {
        return end($this->items);
    }

}
