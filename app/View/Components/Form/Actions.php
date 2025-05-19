<?php

namespace App\View\Components\Form;

use Illuminate\View\Component;

class Actions extends Component
{
    public $show;
    public $edit;
    public $delete;
    public $devolver;
    public $id;

    public function __construct($show = null, $edit = null, $delete = null, $devolver = null, $id = null)
    {
        $this->show = $show;
        $this->edit = $edit;
        $this->delete = $delete;
        $this->devolver = $devolver;
        $this->id = $id;
    }

    public function render()
    {
        return view('components.form.actions');
    }
}
