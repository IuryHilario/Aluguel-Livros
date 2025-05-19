<?php

namespace App\View\Components\Form;

use Illuminate\View\Component;

class Input extends Component
{
    public $type;
    public $name;
    public $id;
    public $label;
    public $value;
    public $placeholder;
    public $required;

    public function __construct(
        $name,
        $type = 'text',
        $id = null,
        $label = null,
        $value = null,
        $placeholder = '',
        $required = false
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->id = $id ?? $name;
        $this->label = $label;
        $this->value = $value;
        $this->placeholder = $placeholder;
        $this->required = $required;
    }

    public function render()
    {
        return view('components.form.input');
    }
}
