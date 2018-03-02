<?php

namespace Lsv\Vertification\Handler;

use Lsv\Vertification\TypeInterface;

class TypeHandler
{
    /**
     * @var TypeInterface[]
     */
    protected $types = [];

    /**
     * Add type to handler.
     *
     * @param TypeInterface $type
     */
    public function addType(TypeInterface $type): void
    {
        $this->types[$type->getKey()] = $type;
    }

    /**
     * @param array|null $options
     *
     * @return TypeInterface[]|array
     */
    public function getTypes(array $options = null): array
    {
        return array_filter(
            $this->types,
            function (TypeInterface $type) use ($options) {
                return $type->isEnabled($options);
            }
        );
    }

    /**
     * Get type from key.
     *
     * @param string     $key
     * @param array|null $options
     *
     * @return TypeInterface
     */
    public function getType($key, array $options = null): TypeInterface
    {
        if (isset($this->types[$key]) && $this->types[$key]->isEnabled($options)) {
            return $this->types[$key];
        }

        throw new \InvalidArgumentException('Type with key "'.$key.'" does not exists');
    }

    /**
     * Get type from class name.
     *
     * @param string     $className
     * @param array|null $options
     *
     * @return TypeInterface
     */
    public function getTypeFromClassName($className, array $options = null): TypeInterface
    {
        foreach ($this->types as $type) {
            if (\get_class($type) === $className && $type->isEnabled($options)) {
                return $type;
            }
        }

        throw new \InvalidArgumentException('Type with className "'.$className.'" does not exists');
    }
}
