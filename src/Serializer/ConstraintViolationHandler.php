<?php

namespace App\Serializer;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

final class ConstraintViolationHandler implements SubscribingHandlerInterface
{
    // registered automatically thanks to symfony auto-wiring
    public static function getSubscribingMethods()
    {
        $methods = [];
        $types = [
            'Symfony\Component\Validator\ConstraintViolationList' => 'serializeList',
            'Symfony\Component\Validator\ConstraintViolation' => 'serializeViolation'
        ];

        foreach ($types as $type => $method) {
            $methods[] = [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'type' => $type,
                'format' => 'json',
                'method' => $method . 'ToJson',
                'priority' => -10,
            ];
        }

        return $methods;
    }

    /**
     * @return array|\ArrayObject
     */
    public function serializeListToJson(SerializationVisitorInterface $visitor, ConstraintViolationList $list, array $type, SerializationContext $context)
    {
        return $visitor->visitArray(iterator_to_array($list), $type);
    }

    public function serializeViolationToJson(SerializationVisitorInterface $visitor, ConstraintViolation $violation, ?array $type = null): array
    {
        return [
            'property_path' => $violation->getPropertyPath(),
            'message' => $violation->getMessage(),
            'error_codes' => $this->getErrorPayloads($violation),
        ];
    }

    private function getErrorPayloads(ConstraintViolation $cause)
    {
        if (!$cause->getConstraint() || empty($cause->getConstraint()->payload['error_code'])) {
            return [];
        }

        return (array)$cause->getConstraint()->payload['error_code'];
    }
}
