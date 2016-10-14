<?php

namespace App\Http\Controllers;

use App\Http\Controllers\QuintypeController;

class PreviewController extends QuintypeController
{
    public function home()
    {
        return view('preview_home', $this->toView([]));
    }

    public function story()
    {
        return view('preview_story', $this->toView([]));
    }
}
