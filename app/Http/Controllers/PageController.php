<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function expenseTips()
    {
        return view('expenses.tips');
    }
}
