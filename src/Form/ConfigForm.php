<?php

namespace UserNames\Form;

use Laminas\Form\Form;

class ConfigForm extends Form
{
    protected $globalSettings;

    public function init()
    {
        $minLengthSetting = $this->globalSettings->get('usernames_min_length');
        $maxLengthSetting = $this->globalSettings->get('usernames_max_length');

        $this->add([
            'type' => 'Number',
            'name' => 'usernames_min_length',
            'options' => [
                'label' => 'User name minimum length', // @translate
            ],
            'attributes' => [
                'value' => $minLengthSetting,
                'id' => 'usernames_min_length',
                'min' => 1,
                'max' => 190,
            ],
        ]);

        $this->add([
            'type' => 'Number',
            'name' => 'usernames_max_length',
            'options' => [
                'label' => 'User name maximum length', // @translate
            ],
            'attributes' => [
                'value' => $maxLengthSetting,
                'id' => 'usernames_max_length',
                'min' => 1,
                'max' => 190,
            ],
        ]);
    }

    public function setGlobalSettings($globalSettings)
    {
        $this->globalSettings = $globalSettings;
    }
}
