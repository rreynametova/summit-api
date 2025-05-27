<?php

namespace App\Services\ExternalLogger\Model;

/**
 * Class LogDataModel
 * Defines the structure for log data.
 * @package App\Services\ExternalLogger
 */
class ApplicationModel extends LogDataModel
{

    public $user = null;

    public $controller = null;

    public $action = null;


    /**
     * Converts the model to an array, useful for serialization or logging.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'user' => $this->user,
            'controller' => $this->controller,
            'action' => $this->action,
        ];
    }
}