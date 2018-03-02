<?php

namespace Lsv\VertificationTest;

use Lsv\Vertification\Handler\TypeHandler;
use Lsv\Vertification\TypeInterface;

trait CreateTestHandlerTrait
{

    /**
     * @param TypeInterface[] $types
     *
     * @return TypeHandler
     */
    protected function getHandler(array $types = []): TypeHandler
    {
        $handler = new TypeHandler();
        foreach ($types as $type) {
            $handler->addType($type);
        }
        return $handler;
    }

}
