<?php

namespace Tangoslee\PostScript\Commands;

abstract class PostScript
{
    public function up()
    {
        throw new \BadMethodCallException('Please implement up() method');
    }

    public function down()
    {
        throw new \BadMethodCallException('Please implement down() method');
    }
}
