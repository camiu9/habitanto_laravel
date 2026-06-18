<?php

namespace App\Http\Controllers\FakeStore;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class FakeStoreDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('fake-store.dashboard');
    }
}
