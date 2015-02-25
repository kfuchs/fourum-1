<?php

namespace Fourum\Validation\Validators;

use Fourum\Validation\ValidatorInterface;
use Respect\Validation\Validator;

class ContentValidator implements ValidatorInterface
{
    /**
     * @var array
     */
    protected $messages;

    /**
     * @param string $value
     * @return boolean
     */
    public function validate($value)
    {
        $validator = Validator::notEmpty();

        try {
            $validator->assert($value);
        } catch (\InvalidArgumentException $e) {
            $this->messages = $e->findMessages($this->getCustomMessages());
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        foreach ($this->messages as $message) {
            if (! empty($message)) {
                return $message;
            }
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'content';
    }

    /**
     * @return array
     */
    protected function getCustomMessages()
    {
        return array(
            'notEmpty' => 'The content of your post cannot be empty.',
            'alnum' => 'The content of your post must only contain alphanumeric characters.'
        );
    }
}
