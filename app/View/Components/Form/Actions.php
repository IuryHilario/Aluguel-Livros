<?php

namespace App\View\Components\Form;

use Illuminate\View\Component;

class Actions extends Component
{
    public $id;
    public $show;
    public $edit;
    public $delete;
    public $devolver;
    public $email;


    public function __construct($id = null, $show = null, $edit = null, $delete = null, $devolver = null, $email = null)
    {
        $this->id = $id;
        $this->show = $show;
        $this->edit = $edit;
        $this->delete = $delete;
        $this->devolver = $devolver;
        $this->email = $email;
    }

    public function render()
    {
        return view('components.form.actions');
    }
}
