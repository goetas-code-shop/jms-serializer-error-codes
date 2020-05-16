<?php

namespace App\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FormErrorHandler implements SubscribingHandlerInterface
{
    private $translator;

    // registered automatically thanks to symfony auto<wiring
    public static function getSubscribingMethods()
    {
        $methods = array();
        $methods[] = array(
            'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
            'type' => Form::class,
            'format' => 'json',
            'priority' => -10,
        );

        return $methods;
    }

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function serializeFormToJson(JsonSerializationVisitor $visitor, Form $form, array $type, Context $context)
    {
        $serializedForm = $this->convertFormToArray($visitor, $form);
        $statusCode = $this->getStatusCode($context);

        if (null !== $statusCode) {
            return [
                'code' => $statusCode,
                'message' => 'Validation Failed',
                'errors' => $serializedForm,
            ];
        }

        return $serializedForm;
    }

    private function getErrorMessage(FormError $error)
    {
        return $this->translator->trans($error->getMessageTemplate(), $error->getMessageParameters(), 'validators');
    }

    private function getErrorPayloads(FormError $error)
    {
        /**
         * @var $cause ConstraintViolation
         */
        $cause = $error->getCause();

        if (!($cause instanceof ConstraintViolation) || !$cause->getConstraint() || empty($cause->getConstraint()->payload['error_code'])) {
            return null;
        }

        return $cause->getConstraint()->payload['error_code'];
    }

    private function convertFormToArray(JsonSerializationVisitor $visitor, Form $data)
    {
        $form = new \ArrayObject();
        $errorCodes = array();
        $errors = array();

        foreach ($data->getErrors() as $error) {
            $errors[] = $this->getErrorMessage($error);
        }

        foreach ($data->getErrors() as $error) {
            $errorCode = $this->getErrorPayloads($error);
            if (is_array($errorCode)) {
                $errorCodes = array_merge($errorCodes, array_values($errorCode));
            } elseif ($errorCode !== null) {
                $errorCodes[] = $errorCode;
            }
        }

        if ($errors) {
            $form['errors'] = $errors;
            if ($errorCodes) {
                $form['error_codes'] = array_unique($errorCodes);
            }
        }

        $children = array();
        foreach ($data->all() as $child) {
            if ($child instanceof Form) {
                $children[$child->getName()] = $this->convertFormToArray($visitor, $child);
            }
        }

        if ($children) {
            $form['children'] = $children;
        }

        return $form;
    }

    private function getStatusCode(Context $context)
    {
        if ($context->hasAttribute('status_code')) {
            return $context->getAttribute('status_code');
        }
    }
}
